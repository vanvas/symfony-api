<?php

declare(strict_types=1);

namespace Vim\Api\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation\Groups;
use Psr\Log\LoggerInterface;
use Vim\Api\Attribute\Hydration\Identity;
use Vim\Api\Attribute\Hydration\Type\HydrationTypeInterface;
use Vim\Api\Service\EntityService;

class DoctrineHydrator
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private EntityService $entityService,
    ) {
    }

    public function hydrate(object $entity, array $data, array $groups = []): object
    {
        $metaData = $this->entityService->getMetaData($entity);
        $entityRef = new \ReflectionClass($this->entityService->getEntityRealNamespace($entity));
        foreach ($data as $propertyName => $value) {
            if (!$propertyRef = ($entityRef->hasProperty($propertyName) ? $entityRef->getProperty($propertyName) : null)) {
                continue;
            }

            if (!$this->isPropertyHydrateAble($propertyRef, $entityRef, $groups)) {
                continue;
            }

            if ($metaData->fieldMappings[$propertyRef->getName()] ?? null) {
                $this->setValue($propertyRef, $entity, $value);

                continue;
            }

            $association = $metaData->associationMappings[$propertyRef->getName()];
            if (in_array($association['type'], [ClassMetadataInfo::MANY_TO_ONE, ClassMetadataInfo::ONE_TO_ONE])) {
                $relationInstance = null === $value ? null : $this->fetchRelationInstance($association['targetEntity'], $propertyRef, $value);
                $this->setValue($propertyRef, $entity, $relationInstance);
            } else if (in_array($association['type'], [ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::ONE_TO_MANY])) {
                $currentCollection = $this->getValue($entity, $propertyRef) ?? new ArrayCollection();
                $plannedCollection = new ArrayCollection();
                foreach ($value as $valueItem) {
                    $relationInstance = $this->fetchRelationInstance($association['targetEntity'], $propertyRef, $valueItem);
                    if (\is_array($valueItem)) {
                        $this->hydrate($relationInstance, $valueItem, $groups);
                    }

                    if ($association['mappedBy'] ?? null) {
                        $this->setValue($association['mappedBy'], $relationInstance, $entity);
                    }

                    $plannedCollection->add($relationInstance);
                }

                foreach ($plannedCollection as $relation) {
                    if (!$currentCollection->contains($relation)) {
                        $currentCollection->add($relation);
                    }
                }

                foreach ($currentCollection as $relation) {
                    if (!$plannedCollection->contains($relation)) {
                        $currentCollection->removeElement($relation);
                    }
                }
            } else {
                $this->logger->error('[SKIPPED] Not found mapping for the "'.$propertyName.'". Data: ' . \json_encode($value));
            }
        }

        return $entity;
    }

    private function isPropertyHydrateAble(\ReflectionProperty $property, \ReflectionClass $entity, array $groups): bool
    {
        $metaData = $this->entityService->getMetaData($entity->getName());
        if (empty($metaData->fieldMappings[$property->getName()]) && empty($metaData->associationMappings[$property->getName()])) {
            return false;
        }

        if ($property->getName() === $this->entityService->getIdentifierName($entity->getName())) {
            return false;
        }

        if (!$groups) {
            return true;
        }

        return (bool) \array_intersect($groups, $this->getGroups($property));
    }

    public function fetchRelationInstance(string $relationClassName, \ReflectionProperty $property, array|string|int|null $value): object
    {
        $relationMetaData = $this->entityService->getMetaData($relationClassName);
        $criteria = [];
        $identifierNames = \is_array($value) ? $this->getIdentifiers($property) : $this->getIdentifiers(new \ReflectionClass($relationClassName));
        foreach ($identifierNames as $identifierName) {
            $identifierValue = \is_array($value) ? ($value[$identifierName] ?? null) : $value;
            if ($identifierTargetEntity = $relationMetaData->associationMappings[$identifierName]['targetEntity'] ?? null) {
                $identifierValue = $this->em->getRepository($identifierTargetEntity)->findOneBy(
                    \array_reduce(
                        $this->getIdentifiers(new \ReflectionClass($identifierTargetEntity)),
                        static function (array $result, string $filedName) use ($identifierValue) {
                            $result[$filedName] = $identifierValue;

                            return $result;
                        },
                        []
                    )
                );
            }

            $criteria[$identifierName] = $identifierValue;
        }

        $relationInstance = null;
        if (\count(\array_filter($criteria)) === \count($criteria)) {
            $relationInstance = $this->em->getRepository($relationClassName)->findOneBy($criteria);
        }

        return $relationInstance ?? new $relationClassName();
    }

    private function getIdentifiers(\ReflectionClass|\ReflectionProperty $subject): array
    {
        /** @var Identity|null $identityAttribute */
        $identityAttribute = ($subject->getAttributes(Identity::class)[0] ?? null)?->newInstance();
        if ($identityAttribute) {
            return $identityAttribute->getPropertyNames();
        }

        return ['id'];
    }

    private function setValue(\ReflectionProperty|string $property, object $entity, mixed $value): void
    {
        if (\is_string($property)) {
            $reflectionEntity = new \ReflectionObject($entity);
            $property = $reflectionEntity->getProperty($property);
        }

        foreach ($this->getPropertyHydrationTypes($property) as $type) {
            $value = $type->convert($value);
        }

        try {
            $entity->{'set' . $property->getName()}($value);
        } catch (\Throwable $exception) {
            if (!preg_match('/call to undefined method/i', $exception->getMessage())) {
                throw $exception;
            }

            $property->setAccessible(true);
            $property->setValue($entity, $value);
        }
    }

    private function getValue(object $entity, \ReflectionProperty $property): mixed
    {
        try {
            $value = $entity->{'get' . $property->getName()}();
        } catch (\Throwable) {
            $property->setAccessible(true);
            $value =  $property->getValue($entity);
        }

        return $value;
    }

    private function getGroups(\ReflectionProperty $property): array
    {
        return ($property->getAttributes(Groups::class)[0] ?? null)?->newInstance()?->groups ?? [];
    }

    /**
     * @return HydrationTypeInterface[]
     */
    private function getPropertyHydrationTypes(\ReflectionProperty $property): array
    {
        return \array_map(
            fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            \array_values(
                \array_filter(
                    $property->getAttributes(),
                    fn (\ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), HydrationTypeInterface::class)
                )
            )
        );
    }
}
