<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\MultiSelectService;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class MultiSelect extends AbstractFilter
{
    public function getService(): string
    {
        return MultiSelectService::class;
    }
}
