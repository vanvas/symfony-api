<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
class Collection
{
    public function __construct(public ?string $defaultSortBy = null, public bool $defaultOrderDesc = true)
    {
    }
}
