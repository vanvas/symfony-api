<?php

declare(strict_types=1);

namespace Vim\Api\Attribute\Hydration\Type;

interface HydrationTypeInterface
{
    public function convert(mixed $value): mixed;
}
