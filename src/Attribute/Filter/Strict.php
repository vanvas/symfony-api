<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\StrictService;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Strict extends AbstractFilter
{
    public function getService(): string
    {
        return StrictService::class;
    }
}
