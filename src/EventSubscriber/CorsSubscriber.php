<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $allowOrigin, private string $allowHeaders) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->isMethod('OPTIONS')) {
            return;
        }

        $response = new Response();
        $event->setResponse($response);
        $this->prepareHeaders($request, $response);
        $response->send();
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $this->prepareHeaders($request, $response);
    }

    private function prepareHeaders(Request $request, Response $response): void
    {
        $response->headers->set('Access-Control-Allow-Headers', $this->allowHeaders);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Allow', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        if ($request->headers->get('origin') && preg_match('~' . $this->allowOrigin . '~', $request->headers->get('origin'))) {
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('origin'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 999],
            KernelEvents::RESPONSE => ['onKernelResponse', -999],
        ];
    }
}
