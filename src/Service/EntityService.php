<?php
declare(strict_types=1);

namespace Vim\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;

class EntityService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getAlias(string|object $entity): string
    {
        return lcfirst((new \ReflectionClass($entity))->getShortName());
    }

    public function getRepository(string|object $entity): ObjectRepository
    {
        return $this->em->getRepository($this->getEntityRealNamespace($entity));
    }

    public function getEntityRealNamespace(string|object $entity): string
    {
        return $this->em->getClassMetadata(is_string($entity) ? $entity : get_class($entity))->getName();
    }

    public function getMetaData(string|object $entity): ClassMetadata
    {
        return $this->em->getClassMetadata($this->getEntityRealNamespace($entity));
    }

    public function getIdentifierName(string|object $entity): string
    {
        return $this->getMetaData($entity)->getIdentifier()[0];
    }
}
