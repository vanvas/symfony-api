<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Attribute\Filter\Search;
use Vim\Api\Exception\UnexpectedTypeException;

class SearchService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder    $qb,
        array $columns,
                        $value,
        string          $paramKey
    ): void
    {
        if (!$filter instanceof Search) {
            throw new UnexpectedTypeException($filter, Search::class);
        }

        if (!trim($value)) {
            return;
        }

        foreach ($columns as $column) {
            $where[] = '(LOWER(' . $column . ') LIKE LOWER(:' . $paramKey . '))';
        }

        if (!$where) {
            return;
        }

        $qb->andWhere(implode(' OR ', $where))->setParameter($paramKey, '%' . $value . '%');
    }
}
