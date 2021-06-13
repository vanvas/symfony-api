<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter\Filter;

use Vim\Api\Attribute\Filter\DateFrom;
use Vim\Api\Attribute\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Vim\Api\Exception\UnexpectedTypeException;
use Psr\Log\LoggerInterface;

class DateFromService implements FilterServiceInterface
{
    public function prepareQuery(
        FilterInterface $filter,
        QueryBuilder $qb,
        string $fieldName,
        $value,
        string $paramKey
    ): void {
        if (!$filter instanceof DateFrom) {
            throw new UnexpectedTypeException($filter, DateFrom::class);
        }

        $date = $value instanceof \DateTimeInterface ? $value : new \DateTimeImmutable($value);

        $qb
            ->andWhere($fieldName . ' >= :' . $paramKey . '_from')
            ->setParameter($paramKey . '_from', $date->setTime(0, 0))
        ;
    }
}
