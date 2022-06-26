<?php

namespace Vim\Api\Attribute\Hydration\Type;

#[\Attribute]
class DateTimeImmutableType implements HydrationTypeInterface
{
    public function convert(mixed $value): mixed
    {
        return $value === '' || $value === null ? null : new \DateTimeImmutable($value);
    }
}
