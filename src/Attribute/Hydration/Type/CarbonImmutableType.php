<?php

namespace Vim\Api\Attribute\Hydration\Type;

use Carbon\CarbonImmutable;

#[\Attribute]
class CarbonImmutableType implements HydrationTypeInterface
{
    public function convert(mixed $value): mixed
    {
        return $value === '' || $value === null ? null : CarbonImmutable::make($value);
    }
}
