<?php
declare(strict_types=1);

namespace Vim\Api\Event;

use Symfony\Component\HttpFoundation\Request;

class ResourceHydrated
{
    public function __construct(private object $resource, private Request $request, private array $data) {}

    public function getResource(): object
    {
        return $this->resource;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
