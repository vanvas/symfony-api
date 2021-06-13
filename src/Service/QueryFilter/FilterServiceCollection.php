<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter;

use Vim\Api\Service\QueryFilter\Filter\FilterServiceInterface;

class FilterServiceCollection
{
    public function __construct(private \Traversable $filters) {}

    public function getByName(string $name): ?FilterServiceInterface
    {
        foreach ($this->filters as $filter) {
            if ($filter::class === $name) {
                return $filter;
            }
        }

        return null;
    }
}
