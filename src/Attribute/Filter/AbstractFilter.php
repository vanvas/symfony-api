<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

abstract class AbstractFilter implements FilterInterface
{
    public function __construct(public string $dbParam, public ?string $requestParam = null) {}

    public function getDbParam(): string
    {
        return $this->dbParam ?? $this->requestParam;
    }

    public function getRequestParam(): string
    {
        return $this->requestParam ?? $this->dbParam;
    }
}
