<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Carbon\CarbonImmutable;
use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Attribute\Filter\IsLessThanCurrentDateTime;
use Vim\Api\Exception\UnexpectedTypeException;

class IsLessThanCurrentDateTimeService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof IsLessThanCurrentDateTime) {
            throw new UnexpectedTypeException($filter, IsLessThanCurrentDateTime::class);
        }
        
        $qb->andWhere($fieldName . ' <= :' . $paramKey)->setParameter($paramKey, CarbonImmutable::now());
    }
}
