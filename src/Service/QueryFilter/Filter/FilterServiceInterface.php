<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;

interface FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void;
}
