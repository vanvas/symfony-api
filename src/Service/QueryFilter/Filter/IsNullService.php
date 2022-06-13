<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\IsNull;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class IsNullService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof IsNull) {
            throw new UnexpectedTypeException($filter, IsNull::class);
        }

        $qb->andWhere($fieldName . ' IS NULL');
    }
}
