<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

interface FilterInterface
{
    public function getDbParam(): string;

    public function getRequestParam(): string;

    public function getService(): string;
}
