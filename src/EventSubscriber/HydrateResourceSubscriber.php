<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Vim\Api\Attribute\Groups;
use Vim\Api\Attribute\Hydrate;
use Vim\Api\Attribute\Resource;
use Vim\Api\Event\ResourceHydrated;
use Vim\Api\Exception\LogicException;
use Vim\Api\Service\HydrationService;
use Vim\Api\Service\RequestAttributeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HydrateResourceSubscriber implements EventSubscriberInterface
{
    private const AVAILABLE_METHODS = [
        'POST',
        'PUT',
        'PATCH',
    ];

    public function __construct(
        private RequestAttributeService $requestAttributeService,
        private HydrationService $hydrationService,
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
    )
    {}

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();

        /** @var Hydrate|null $hydrateConfig */
        $hydrateConfig = $this->requestAttributeService->getAttributeForCurrentAction($request, Hydrate::class);
        if (!$hydrateConfig) {
            return;
        }

        if (!in_array($request->getMethod(), self::AVAILABLE_METHODS, true)) {
            throw new LogicException(
                'Hydration is supported only by the following methods: "' . implode(',', self::AVAILABLE_METHODS) . '"'
            );
        }

        /** @var Resource|null $resource */
        $resource = $this->requestAttributeService->getAttributeForCurrentAction($request, Resource::class);
        if (!$resource || !$entity = $request->get($resource->entity)) {
            throw new LogicException(
                'Resource not found or has not been configured. Use ' . Resource::class . ' for configuration.'
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if ($hydrateConfig->fields) {
            $data = array_filter(
                $data,
                function (string $key) use ($hydrateConfig) {
                    return in_array($key, $hydrateConfig->fields, true);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        $authUser = $this->security->getUser();
        /** @var Groups|null $groupAttribute */
        $groupAttribute = $this->requestAttributeService->getAttributeForCurrentAction($request, Groups::class);
        $groups = $groupAttribute ? array_merge($groupAttribute->groups, $authUser?->getRoles() ?? []) : [];

        $this->hydrationService->hydrateEntity($entity, $data, $groups);
        $this->eventDispatcher->dispatch(new ResourceHydrated($entity, $request, $data));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments', -1],
        ];
    }
}
