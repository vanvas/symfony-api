<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\IsLessThanCurrentDateTimeService;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class IsLessThanCurrentDateTime extends AbstractFilter implements DefaultFilterInterface
{
    public function getService(): string
    {
        return IsLessThanCurrentDateTimeService::class;
    }
}
