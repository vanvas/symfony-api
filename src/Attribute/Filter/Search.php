<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\SearchService;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Search extends AbstractFilter
{
    public function getService(): string
    {
        return SearchService::class;
    }
}
