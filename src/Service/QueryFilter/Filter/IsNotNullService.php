<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\IsNotNull;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class IsNotNullService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof IsNotNull) {
            throw new UnexpectedTypeException($filter, IsNotNull::class);
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $qb->andWhere($column . ' IS NOT NULL');
        }
    }
}
