<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Vim\Api\Event\ResourceValidationFailed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceValidationFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ResourceValidationFailed::class => 'onResourceValidationFailed'
        ];
    }

    public function onResourceValidationFailed(ResourceValidationFailed $event): void
    {
        $entity = $event->getResource();
        if ($this->em->contains($entity)) {
            $this->em->refresh($entity);
        }
    }
}
