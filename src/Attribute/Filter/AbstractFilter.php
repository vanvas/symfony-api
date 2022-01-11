<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

abstract class AbstractFilter implements FilterInterface
{
    public function __construct(
        public string $dbParam,
        public ?string $requestParam = null,
        public ?string $routeName = null,
        public ?array $routeParameters = null,
        public ?array $values = null,
        public ?array $context = null,
    ) {}

    public function getDbParam(): string
    {
        return $this->dbParam ?? $this->requestParam;
    }

    public function getRequestParam(): string
    {
        return $this->requestParam ?? $this->dbParam;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getRouteParameters(): ?array
    {
        return $this->routeParameters;
    }

    public function getValues(): ?array
    {
        return $this->values;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
