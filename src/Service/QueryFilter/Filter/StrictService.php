<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\Strict;
use Vim\Api\Attribute\Filter\StrictInsensitive;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class StrictService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof Strict) {
            throw new UnexpectedTypeException($filter, Strict::class);
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $where = $column . ' = :' . $paramKey;
            if ($filter instanceof StrictInsensitive) {
                $where = 'LOWER(' . $column . ') = LOWER(:' . $paramKey.')';
            }

            $qb->andWhere($where);
        }

        $qb->setParameter($paramKey, $value);
    }
}
