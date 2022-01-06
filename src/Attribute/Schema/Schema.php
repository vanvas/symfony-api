<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema;

#[\Attribute]
final class Schema
{
    public function __construct(public string $className) {}
}
