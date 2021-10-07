<?php
declare(strict_types=1);

namespace Vim\Api\Event;

use Vim\Api\Request\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestArgumentValidated
{
    public function __construct(private Request $request, private RequestInterface $argument) {}

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getArgument(): RequestInterface
    {
        return $this->argument;
    }
}
