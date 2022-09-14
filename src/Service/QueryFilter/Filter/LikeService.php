<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\Like;
use Vim\Api\Attribute\Filter\LikeInsensitive;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class LikeService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof Like) {
            throw new UnexpectedTypeException($filter, Like::class);
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $where = $column . ' LIKE :' . $paramKey;
            if ($filter instanceof LikeInsensitive) {
                $where = 'LOWER(' . $column . ') LIKE LOWER(:' . $paramKey.')';
            }

            $qb->andWhere($where);
        }

        $qb->setParameter($paramKey, '%' . $value . '%');
    }
}
