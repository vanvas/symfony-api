<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class StrictInsensitive extends Strict
{
}
