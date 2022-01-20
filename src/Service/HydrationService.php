<?php
declare(strict_types=1);

namespace Vim\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Vim\Api\Hydrator\DoctrineHydrator;

class HydrationService
{
    public function __construct(private DoctrineHydrator $doctrineHydrator, private EntityManagerInterface $em)
    {
    }

    public function hydrateEntity(object $entity, array $data, array $groups = []): void
    {
        try {
            $this->doctrineHydrator->hydrate($entity, $data, $groups);
        } catch (\Throwable $exception) {
            if ($this->em->contains($entity)) {
                $this->em->refresh($entity);
            }

            throw $exception;
        }
    }
}
