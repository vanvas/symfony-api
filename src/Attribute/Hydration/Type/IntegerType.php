<?php

namespace Vim\Api\Attribute\Hydration\Type;

#[\Attribute]
class IntegerType implements HydrationTypeInterface
{
    public function convert(mixed $value): ?int
    {
        return $value === '' || $value === null ? null : (int) $value;
    }
}
