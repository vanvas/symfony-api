<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\Like;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class LikeService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof Like) {
            throw new UnexpectedTypeException($filter, Like::class);
        }

        $qb
            ->andWhere($fieldName . ' LIKE :' . $paramKey)
            ->setParameter($paramKey, '%' . $value . '%')
        ;
    }
}
