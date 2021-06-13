<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\LikeService;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Like extends AbstractFilter
{
    public function getService(): string
    {
        return LikeService::class;
    }
}
