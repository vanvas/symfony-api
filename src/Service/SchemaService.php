<?php

declare(strict_types=1);

namespace Vim\Api\Service;

use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Routing\RouterInterface;
use Vim\Api\Attribute\Schema\Type\ChoiceType;
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
            if (!$this->canSerializeProperty($property, $serializationGroups)) {
                continue;
            }
            
            $attribute = $this->getAttributes($property)[0];
            $result[] = new SchemaItem(
                $property,
                $attribute,
                $this->getEmbedded($attribute, $serializationGroups),
                $this->getListUrl($attribute),
            );
        }

        foreach ($reflectionClass->getMethods() as $method) {
            if (!$this->canSerializeProperty($method, $serializationGroups)) {
                continue;
            }

            if (!$attribute = $this->getAttributes($method)[0] ?? null) {
                continue;
            }

            $result[] = new SchemaItem(
                $method,
                $attribute,
                $this->getEmbedded($attribute, $serializationGroups),
                $this->getListUrl($attribute),
            );
        }

        foreach ($this->getAttributes($reflectionClass) as $attribute) {
            if ($attribute->getGroups() && !array_intersect($attribute->getGroups(), $serializationGroups)) {
                continue;
            }

            $result[] = new SchemaItem(
                $reflectionClass,
                $attribute,
                $this->getEmbedded($attribute, $serializationGroups),
                $this->getListUrl($attribute),
            );
        }

        uasort($result, function (SchemaItem $a, SchemaItem $b) {
            return (int) $b->getPriority() <=> (int) $a->getPriority();
        });
        
        return array_values($result);
    }

    private function getEmbedded(SchemaTypeInterface $attribute, array $serializationGroups): ?array
    {
        $embedded = null;
        if ($attribute instanceof EmbeddedType) {
            if (!$embedded = $this->getSchema($attribute->className, $serializationGroups)) {
                return null;
            }
        }

        return $embedded;
    }

    private function getListUrl(SchemaTypeInterface $attribute): ?string
    {
        $listUrl = null;
        if (($attribute instanceof RelationType || $attribute instanceof ChoiceType) && $attribute->routeName) {
            $listUrl = $this->router->generate($attribute->routeName, $attribute->routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $listUrl;
    }

    private function canSerializeProperty(\ReflectionProperty|\ReflectionMethod $subject, array $serializationGroups): bool
    {
        if (!$serializationGroups) {
            return true;
        }

        foreach ($subject->getAttributes(Groups::class) as $groupReflectionAttribute) {
            /** @var Groups $groupAttribute */
            $groupAttribute = $groupReflectionAttribute->newInstance();
            if (array_intersect($groupAttribute->groups, $serializationGroups)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param \ReflectionProperty|\ReflectionClass|\ReflectionMethod $subject
     * @return SchemaTypeInterface[]
     */
    private function getAttributes(\ReflectionProperty|\ReflectionClass|\ReflectionMethod $subject): array
    {
        /** @var \ReflectionAttribute[] $attributes */
        $attributes = array_map(
            fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            array_values(
                array_filter(
                    $subject->getAttributes(),
                    fn(\ReflectionAttribute $reflectionAttribute) => is_subclass_of($reflectionAttribute->getName(), SchemaTypeInterface::class)
                )
            )
        );

        if ($attributes || $subject instanceof \ReflectionClass || $subject instanceof \ReflectionMethod) {
            return $attributes;
        }

        $reflectionType = $subject->getType();
        if ($reflectionType instanceof \ReflectionUnionType) {
            $type = array_map(fn (\ReflectionNamedType $reflectionNamedType) => $reflectionNamedType->getName(), $reflectionType->getTypes())[0];
        } elseif ($reflectionType instanceof \ReflectionNamedType) {
            $type = $reflectionType->getName();
        } else {
            throw new \LogicException('It needs to implement logic for the "'.$reflectionType::class.'"');
        }

        if (class_exists($type) || interface_exists($type)) {
            if (is_subclass_of($type, \DateTimeInterface::class)) {
                return [new DatetimeType()];
            }

            throw new SchemaAttributeNotSetException($subject);
        }

        return match ($type) {
            'int', 'integer', 'float' => [new NumberType()],
            'string' => [new StringType()],
            'array' => throw new SchemaAttributeNotSetException($subject),
            default => [new CustomType($type)],
        };
    }
}
