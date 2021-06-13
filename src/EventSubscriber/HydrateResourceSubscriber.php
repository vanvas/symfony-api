<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Vim\Api\Attribute\Hydrate;
use Vim\Api\Attribute\Resource;
use Vim\Api\Exception\LogicException;
use Vim\Api\Service\RequestAttributeService;
use pmill\Doctrine\Hydrator\ArrayHydrator;
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
        private ArrayHydrator $hydrator
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

        $this->hydrator->hydrate($entity, $data);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments', -1],
        ];
    }
}
