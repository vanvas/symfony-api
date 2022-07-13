<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\DateWithinDay;
use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;

class DateWithinDayService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof DateWithinDay) {
            throw new UnexpectedTypeException($filter, DateWithinDay::class);
        }

        if (!$value instanceof \DateTimeInterface) {
            $value = class_exists(\Carbon\CarbonImmutable::class) ? new \Carbon\CarbonImmutable($value) : new \DateTimeImmutable($value);
        }

        $qb
            ->andWhere($fieldName . ' >= :' . $paramKey . '_from')
            ->andWhere($fieldName . ' <= :' . $paramKey . '_to')
            ->setParameter($paramKey . '_from', $value->setTime(0, 0))
            ->setParameter($paramKey . '_to', $value->setTime(23, 59, 59))
        ;
    }
}
