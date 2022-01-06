<?php

declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Vim\Api\Attribute\Groups;
use Vim\Api\Attribute\Schema\Schema;
use Vim\Api\Service\SchemaService;

class SchemaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private Security            $security,
        private SchemaService       $schemaService,
        private RouterInterface     $router,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!in_array($exception->getCode(), [404, 405]) && !($exception instanceof HttpExceptionInterface && in_array($exception->getStatusCode(), [404, 405]))) {
            return;
        }

        $request = $event->getRequest();
        $segments = explode('/', $request->getPathInfo());
        if (!in_array('attributes', $segments, true) || $segments[array_key_last($segments)] !== 'attributes') {
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

        /** @var Schema $schemaAttribute */
        $schemaAttribute = $schemaReflectionAttribute[0]->newInstance();

        $authUser = $this->security->getUser();
        /** @var Groups|null $groupAttribute */
        $groupAttribute = ($method->getAttributes(Groups::class)[0] ?? null)?->newInstance();
        $groups = $groupAttribute ? array_merge($groupAttribute->groups, $authUser?->getRoles() ?? []) : [];

        $event->setResponse(
            new JsonResponse([
                'data' => $this->serializer->toArray(
                    $this->schemaService->getSchema($schemaAttribute->className, $groups)
                ),
            ])
        );

        $event->allowCustomResponseCode();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 50]
        ];
    }
}
