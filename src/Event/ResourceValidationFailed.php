<?php
declare(strict_types=1);

namespace Vim\Api\Event;

class ResourceValidationFailed
{
    public function __construct(private object $resource) {}

    public function getResource(): object
    {
        return $this->resource;
    }
}
