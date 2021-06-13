<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
final class Groups
{
    public function __construct(public array $groups)
    {
    }
}
