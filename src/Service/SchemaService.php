<?php

declare(strict_types=1);

namespace Vim\Api\Service;

use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Routing\RouterInterface;
use Vim\Api\Attribute\Schema\Type\CustomType;
use Vim\Api\Attribute\Schema\Type\DatetimeType;
use Vim\Api\Attribute\Schema\Type\EmbeddedType;
use Vim\Api\Attribute\Schema\Type\NumberType;
use Vim\Api\Attribute\Schema\Type\RelationType;
use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;
use Vim\Api\Attribute\Schema\Type\StringType;
use Vim\Api\DTO\SchemaItem;
use Vim\Api\Exception\SchemaAttributeNotSetException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchemaService
{
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * @return SchemaItem[]
     */
    public function getSchema(string $className, array $serializationGroups): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($className);
        foreach ($reflectionClass->getProperties() as $property) {
            if (!$this->canSerialize($property, $serializationGroups)) {
                continue;
            }
            
            $attribute = $this->getTypeAttribute($property);
            $embedded = null;
            if ($attribute instanceof EmbeddedType) {
                if (!$embedded = $this->getSchema($attribute->className, $serializationGroups)) {
                    continue;
                }
            }

            $listUrl = null;
            if ($attribute instanceof RelationType && $attribute->routeName) {
                $listUrl = $this->router->generate($attribute->routeName, $attribute->routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);
            }
            
            $result[] = new SchemaItem(
                $property,
                $attribute,
                $embedded,
                $listUrl,
            );
        }
        
        return $result;
    }

    private function canSerialize(\ReflectionProperty $property, array $serializationGroups): bool
    {
        if (!$serializationGroups) {
            return true;
        }

        foreach ($property->getAttributes(Groups::class) as $groupReflectionAttribute) {
            /** @var Groups $groupAttribute */
            $groupAttribute = $groupReflectionAttribute->newInstance();
            if (array_intersect($groupAttribute->groups, $serializationGroups)) {
                return true;
            }
        }
        
        return false;
    }

    private function getTypeAttribute(\ReflectionProperty $property): SchemaTypeInterface
    {
        /** @var \ReflectionAttribute[] $attributes */
        $attributes = array_values(
            array_filter(
                $property->getAttributes(),
                fn(\ReflectionAttribute $reflectionAttribute) => is_subclass_of($reflectionAttribute->getName(), SchemaTypeInterface::class)
            )
        );

        /** @var SchemaTypeInterface|null $attribute */
        if ($attribute = ($attributes[0] ?? null)?->newInstance()) {
            return $attribute;
        }

        $reflectionType = $property->getType();
        if ($reflectionType instanceof \ReflectionUnionType) {
            $type = array_map(fn (\ReflectionNamedType $reflectionNamedType) => $reflectionNamedType->getName(), $reflectionType->getTypes())[0];
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $reflectionType->getName();
        } else {
            throw new \LogicException('It needs to implement logic for the "'.$reflectionType::class.'"');
        }

        if (class_exists($type) || interface_exists($type)) {
            if (is_subclass_of($type, \DateTimeInterface::class)) {
                return new DatetimeType();
            }

            throw new SchemaAttributeNotSetException($property);
        }

        return match ($type) {
            'int', 'integer', 'float' => new NumberType(),
            'string' => new StringType(),
            'array' => throw new SchemaAttributeNotSetException($property),
            default => new CustomType($type),
        };
    }
}
