<?php

declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Vim\Api\Attribute\Groups;
use Vim\Api\Attribute\Schema\Schema;
use Vim\Api\Exception\SchemaRouteException;
use Vim\Api\Service\SchemaService;

class SchemaSubscriber implements EventSubscriberInterface
{
    private const SCHEMA_REQUEST_SEGMENT = '_schema';

    public function __construct(
        private SerializerInterface $serializer,
        private Security            $security,
        private SchemaService       $schemaService,
        private RouterInterface     $router,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $segments = explode('/', $request->getPathInfo());
        if (!in_array(self::SCHEMA_REQUEST_SEGMENT, $segments, true) || $segments[array_key_last($segments)] !== self::SCHEMA_REQUEST_SEGMENT) {
            return;
        }

        array_pop($segments);

        $pathInfo = implode('/', $segments);

        $match = $this->router->match($pathInfo);
        preg_match('/(?<className>[a-z\\\]+)::(?<methodName>[a-z]+)/i', $match['_controller'], $matches);

        $method = (new \ReflectionClass($matches['className']))->getMethod($matches['methodName']);

        if (!$schemaReflectionAttribute = $method->getAttributes(Schema::class)) {
            return;
        }

        $authUser = $this->security->getUser();
        /** @var Groups|null $groupAttribute */
        $groupAttribute = ($method->getAttributes(Groups::class)[0] ?? null)?->newInstance();
        $groups = $groupAttribute ? array_merge($groupAttribute->groups, $authUser?->getRoles() ?? []) : [];
        /** @var Schema $schema */
        $schema = $schemaReflectionAttribute[0]->newInstance();

        throw new SchemaRouteException($schema, $groups);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof SchemaRouteException) {
            return;
        }

        $event->setResponse(
            new JsonResponse([
                'data' => $this->serializer->toArray(
                    $this->schemaService->getSchema($throwable->getSchema()->className, $throwable->getGroups())
                ),
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
