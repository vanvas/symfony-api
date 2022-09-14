<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\DatetimeTo;
use Vim\Api\Attribute\Filter\DateTo;
use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class DateToService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof DateTo) {
            throw new UnexpectedTypeException($filter, DateTo::class);
        }

        if (!$value instanceof \DateTimeInterface) {
            $value = class_exists(\Carbon\CarbonImmutable::class) ? new \Carbon\CarbonImmutable($value) : new \DateTimeImmutable($value);
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $qb->andWhere($fieldName . ' <= :' . $paramKey . '_to');
        }

        $qb->setParameter($paramKey . '_to', $filter instanceof DatetimeTo ? $value : $value->setTime(23, 59, 59));
    }
}
