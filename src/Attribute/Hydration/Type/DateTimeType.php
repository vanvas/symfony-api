<?php

namespace Vim\Api\Attribute\Hydration\Type;

#[\Attribute]
class DateTimeType implements HydrationTypeInterface
{
    public function convert(mixed $value): ?int
    {
        return $value === '' || $value === null ? null : new \DateTime($value);
    }
}
