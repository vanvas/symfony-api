<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Vim\Api\Attribute\Resource;
use Vim\Api\Attribute\Validate;
use Vim\Api\Event\ResourceValidationFailed;
use Vim\Api\Exception\LogicException;
use Vim\Api\Exception\ValidationException;
use Vim\Api\Service\RequestAttributeService;
use Vim\Api\Service\ValidationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ValidateResourceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestAttributeService $requestAttributeService,
        private ValidationService $validationService,
        private EventDispatcherInterface $eventDispatcher
    )
    {}

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requestAttributeService->getAttributeForCurrentAction($request, Validate::class)) {
            return;
        }

        /** @var Resource|null $resource */
        $resource = $this->requestAttributeService->getAttributeForCurrentAction($request, Resource::class);
        if (!$resource || !$entity = $request->get($resource->entity)) {
            throw new LogicException(
                'Resource not found or has not been configured. Use ' . Resource::class . ' for configuration.'
            );
        }

        try {
            $this->validationService->validateObject($entity);
        } catch (ValidationException $exception) {
            $this->eventDispatcher->dispatch(new ResourceValidationFailed($entity));

            throw $exception;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments', -2],
        ];
    }
}
