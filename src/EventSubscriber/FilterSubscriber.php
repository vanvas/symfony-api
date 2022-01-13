<?php

declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\DTO\FilterItem;
use Vim\Api\Exception\FilterRouteException;

class FilterSubscriber implements EventSubscriberInterface
{
    private const FILTER_QUERY_PARAMETER = '_filter';

    public function __construct(
        private SerializerInterface $serializer,
        private RouterInterface     $router,
    ) {
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->get(self::FILTER_QUERY_PARAMETER)) {
            return;
        }

        $match = $this->router->match($request->getPathInfo());
        preg_match('/(?<className>[a-z\\\]+)::(?<methodName>[a-z]+)/i', $match['_controller'], $matches);
        $method = (new \ReflectionClass($matches['className']))->getMethod($matches['methodName']);
        $filterAttributes = array_map(
            fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            array_values(
                array_filter(
                    $method->getAttributes(),
                    fn (\ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), FilterInterface::class)
                )
            )
        );
        if (!$filterAttributes) {
            return;
        }

        throw new FilterRouteException(array_map(
            function (FilterInterface $attribute) {
                return new FilterItem(
                    $attribute,
                    $attribute->getRouteName() ? $this->router->generate($attribute->getRouteName(), $attribute->getRouteParameters() ?? [], UrlGeneratorInterface::ABSOLUTE_URL) : null,
                );
            },
            $filterAttributes
        ));
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof FilterRouteException) {
            return;
        }

        $event->setResponse(
            new JsonResponse([
                'data' => $this->serializer->toArray($throwable->getFilters()),
            ])
        );

        $event->allowCustomResponseCode();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 50],
            KernelEvents::EXCEPTION => ['onKernelException', 50],
        ];
    }
}
