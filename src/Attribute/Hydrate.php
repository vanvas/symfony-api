<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
final class Hydrate
{
    public array $fields = [];
}
