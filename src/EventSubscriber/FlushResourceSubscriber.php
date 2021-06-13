<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Vim\Api\Attribute\Flush;
use Vim\Api\Attribute\Resource;
use Vim\Api\Exception\LogicException;
use Vim\Api\Service\RequestAttributeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FlushResourceSubscriber implements EventSubscriberInterface
{
    private const AVAILABLE_METHODS = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    public function __construct(
        private RequestAttributeService $requestAttributeService,
        private EntityManagerInterface $em
    )
    {}

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requestAttributeService->getAttributeForCurrentAction($request, Flush::class)) {
            return;
        }

        if (!in_array($request->getMethod(), self::AVAILABLE_METHODS, true)) {
            throw new LogicException(
                'Flushing is supported only by the following methods: "' . implode(',', self::AVAILABLE_METHODS) . '"'
            );
        }

        /** @var Resource|null $resource */
        $resource = $this->requestAttributeService->getAttributeForCurrentAction($request, Resource::class);
        if (!$resource || !$entity = $request->get($resource->entity)) {
            throw new LogicException(
                'Resource not found or has not been configured. Use ' . Resource::class . ' for configuration.'
            );
        }

        if ($request->isMethod('DELETE')) {
            $this->em->remove($entity);
        } elseif ($request->isMethod('POST')) {
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments', -3],
        ];
    }
}
