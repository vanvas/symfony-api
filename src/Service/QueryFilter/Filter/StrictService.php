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
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof Strict) {
            throw new UnexpectedTypeException($filter, Strict::class);
        }
        
        $where = $fieldName . ' = :' . $paramKey;
        if ($filter instanceof StrictInsensitive) {
            $where = 'LOWER(' . $fieldName . ') = LOWER(:' . $paramKey.')';
        }

        $qb->andWhere($where)->setParameter($paramKey, $value);
    }
}
