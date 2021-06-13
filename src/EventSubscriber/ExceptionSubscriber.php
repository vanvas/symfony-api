<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Vim\Api\Exception\ExceptionInterface;
use Vim\Api\Exception\ValidationException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $env, private LoggerInterface $logger) {}

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ValidationException) {
            $payload = [
                '_context' => [
                    'code' => ExceptionInterface::VALIDATION_ERROR,
                ],
                'data' => $exception->getErrors(),
            ];
        } elseif (in_array($exception->getCode(), [404, 405])
            || ($exception instanceof HttpExceptionInterface && in_array($exception->getStatusCode(), [404, 405]))
        ) {
            $payload = [
                '_context' => [
                    'code' => ExceptionInterface::NOT_FOUND,
                ],
                'data' => [
                    'message' => $this->env === 'dev'
                        ? $exception->getMessage() . ' :: ' . $exception->getFile() . ' :: ' . $exception->getLine()
                        : 'Not found',
                ],
            ];
        } elseif (in_array($exception->getCode(), [401, 403])) {
            $payload = [
                '_context' => [
                    'code' => $exception->getCode(),
                ],
                'data' => [
                    'message' => 'Access Denied.',
                ],
            ];
        } else {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
            ]);

            $payload = [
                '_context' => [
                    'code' => ExceptionInterface::UNDEFINED_ERROR,
                ],
                'data' => [
                    'message' => $this->env === 'dev'
                        ? [
                            'error' => $exception->getMessage() . ' :: ' . $exception->getFile()
                                . ' :: ' . $exception->getLine(),
                            'trace' => $exception->getTrace(),
                        ]
                        : 'Undefined error',
                ],
            ];
        }

        $event->setResponse(new JsonResponse($payload, $payload['_context']['code']));
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 30]
        ];
    }
}
