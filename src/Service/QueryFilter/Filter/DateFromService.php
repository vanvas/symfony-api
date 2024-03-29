<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\DateFrom;
use Vim\Api\Attribute\Filter\DatetimeFrom;
use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;
use Psr\Log\LoggerInterface;

class DateFromService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        array $columns,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof DateFrom) {
            throw new UnexpectedTypeException($filter, DateFrom::class);
        }
        
        if (!$value instanceof \DateTimeInterface) {
            $value = class_exists(\Carbon\CarbonImmutable::class) ? new \Carbon\CarbonImmutable($value) : new \DateTimeImmutable($value);
        }

        if (!$columns) {
            return;
        }

        foreach ($columns as $column) {
            $qb->andWhere($column . ' >= :' . $paramKey . '_from');
        }

        $qb->setParameter($paramKey . '_from', $filter instanceof DatetimeFrom ? $value : $value->setTime(0, 0));
    }
}
