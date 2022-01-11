<?php

declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\DTO\FilterItem;
use Vim\Api\Exception\FilterRouteException;

class FilterSubscriber implements EventSubscriberInterface
{
    private const FILTER_REQUEST_SEGMENT = '_filter';

    public function __construct(
        private SerializerInterface $serializer,
        private RouterInterface     $router,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $segments = explode('/', $request->getPathInfo());
        if (!in_array(self::FILTER_REQUEST_SEGMENT, $segments, true) || $segments[array_key_last($segments)] !== self::FILTER_REQUEST_SEGMENT) {
            return;
        }

        array_pop($segments);

        $pathInfo = implode('/', $segments);

        $router = clone $this->router;
        $router->setContext((new RequestContext())->fromRequest($request));

        $match = $router->match($pathInfo);
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

        throw new FilterRouteException(
            array_map(
                function (FilterInterface $attribute) {
                    return new FilterItem(
                        $attribute,
                        $attribute->getRouteName() ? $this->router->generate($attribute->getRouteName(), $attribute->getRouteParameters() ?? [], UrlGeneratorInterface::ABSOLUTE_URL) : null,
                    );
                },
                $filterAttributes
            )
        );
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
            KernelEvents::EXCEPTION => ['onKernelException', 50],
            KernelEvents::REQUEST => ['onRequest', 50],
        ];
    }
}
