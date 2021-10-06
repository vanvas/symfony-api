<?php
declare(strict_types=1);

namespace Vim\Api\Event;

use Vim\Api\Request\RequestInterface;

class RequestArgumentValidated
{
    public function __construct(private RequestInterface $argument) {}

    public function getArgument(): RequestInterface
    {
        return $this->argument;
    }
}
