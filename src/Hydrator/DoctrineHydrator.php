<?php

declare(strict_types=1);

namespace Vim\Api\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation\Groups;
use Psr\Log\LoggerInterface;
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
        foreach ($data as $property => $value) {
            if (!$this->isHydrateable($entity, $property, $groups)) {
                continue;
            }

            if ($metaData->fieldMappings[$property] ?? null) {
                $this->setValue($entity, $property, $value);

                continue;
            }

            $association = $metaData->associationMappings[$property];
            if (in_array($association['type'], [ClassMetadataInfo::MANY_TO_ONE, ClassMetadataInfo::ONE_TO_ONE])) {
                $relation = $value ? $this->em->getRepository($association['targetEntity'])->find($value) : null;
                $this->setValue($entity, $property, $relation);
            } else if (in_array($association['type'], [ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::ONE_TO_MANY])) {
                $currentCollection = $this->getValue($entity, $property) ?? new ArrayCollection();
                $plannedCollection = new ArrayCollection();
                $relationClassName = $association['targetEntity'];
                $relationRepository = $this->em->getRepository($relationClassName);
                foreach ($value as $valueItem) {
                    if (is_array($valueItem)) {
                        $id = $valueItem[$this->entityService->getIdentifierName($relationClassName)] ?? null;
                        $relation = $id ? $relationRepository->find($id) : new $relationClassName();
                        if (!$relation = ($id ? $relationRepository->find($id) : new $relationClassName())) {
                            $this->logger->error('[SKIPPED] Relation not found. ID="'.$id.'". Data: ' . print_r($valueItem, true));
                        }

                        $this->hydrate($relation, $valueItem, $groups);
                    } else {
                        if (!$relation = $relationRepository->find($value)) {
                            $this->logger->error('[SKIPPED] Relation not found. ID="'.$value.'". Data: ' . print_r($valueItem, true));

                            continue;
                        }
                    }

                    if ($association['mappedBy'] ?? null) {
                        $this->setValue($relation, $association['mappedBy'], $entity);
                    }

                    $plannedCollection->add($relation);
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
                $this->logger->error('[SKIPPED] Not found mapping for the "'.$property.'". Data: ' . print_r($value, true));
            }
        }

        return $entity;
    }

    private function isHydrateable(object $entity, string $property, array $groups): bool
    {
        $metaData = $this->entityService->getMetaData($entity);
        if (empty($metaData->fieldMappings[$property]) && empty($metaData->associationMappings[$property])) {
            return false;
        }

        if ($property === $this->entityService->getIdentifierName($entity)) {
            return false;
        }

        if (!$groups) {
            return true;
        }

        return (bool) array_intersect($groups, $this->getPropertyGroups($entity, $property));
    }

    private function setValue(object $entity, string $propertyName, mixed $value): void
    {
        foreach ($this->getPropertyHydrationTypes($entity, $propertyName) as $type) {
            $value = $type->convert($value);
        }
        
        try {
            $entity->{'set' . $propertyName}($value);
        } catch (\Throwable $exception) {
            if (!preg_match('/call to undefined method/i', $exception->getMessage())) {
                throw $exception;
            }

            $reflectionEntity = new \ReflectionClass($entity);
            $property = $reflectionEntity->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($entity, $value);
        }
    }

    private function getValue(object $entity, string $propertyName): mixed
    {
        $reflectionEntity = new \ReflectionClass($entity);

        try {
            $value = $entity->{'get' . $propertyName}();
        } catch (\Throwable) {
            $property = $reflectionEntity->getProperty($propertyName);
            $property->setAccessible(true);
            $value =  $property->getValue($entity);
        }

        return $value;
    }

    private function getPropertyGroups(object $entity, string $propertyName): array
    {
        $reflectionEntity = new \ReflectionClass($this->entityService->getEntityRealNamespace($entity));
        $reflectionProperty = $reflectionEntity->getProperty($propertyName);
        
        return ($reflectionProperty->getAttributes(Groups::class)[0] ?? null)?->newInstance()?->groups ?? [];
    }

    /**
     * @return HydrationTypeInterface[]
     */
    private function getPropertyHydrationTypes(object $entity, string $propertyName): array
    {
        $reflectionEntity = new \ReflectionClass($this->entityService->getEntityRealNamespace($entity));
        $reflectionProperty = $reflectionEntity->getProperty($propertyName);

        return array_map(
            fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            array_values(
                array_filter(
                    $reflectionProperty->getAttributes(),
                    fn (\ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), HydrationTypeInterface::class)
                )
            )
        );
    }
}
