<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

interface FilterInterface
{
    public function getDbParam(): string;

    public function getRequestParam(): string;

    public function getRouteName(): ?string;

    public function getRouteParameters(): ?array;

    public function getValues(): ?array;

    public function getContext(): ?array;

    public function getService(): string;
}
