<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\MultiSelect;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class MultiSelectService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof MultiSelect) {
            throw new UnexpectedTypeException($filter, MultiSelect::class);
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $qb->andWhere($column . ' IN (:' . $paramKey . ')');
        }

        $qb->setParameter($paramKey, $value);
    }
}
