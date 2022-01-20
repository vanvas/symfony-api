<?php

namespace Vim\Api\Attribute\Hydration\Type;

use Carbon\Carbon;

#[\Attribute]
class CarbonType implements HydrationTypeInterface
{
    public function convert(mixed $value): ?int
    {
        return $value === '' || $value === null ? null : Carbon::make($value);
    }
}
