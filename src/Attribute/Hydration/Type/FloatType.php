<?php

namespace Vim\Api\Attribute\Hydration\Type;

#[\Attribute]
class FloatType implements HydrationTypeInterface
{
    public function convert(mixed $value): ?float
    {
        return $value === '' || $value === null ? null : (float) $value;
    }
}
