<?php
declare(strict_types=1);

namespace Vim\Api\Event;

use Symfony\Component\HttpFoundation\Request;

class ResourceFlushed
{
    public function __construct(private object $resource, private Request $request) {}

    public function getResource(): object
    {
        return $this->resource;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
