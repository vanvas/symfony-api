<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Filter;

use Vim\Api\Service\QueryFilter\Filter\DateWithinDayService;

#[\Attribute]
class DateWithinDay extends AbstractFilter
{
    public function getService(): string
    {
        return DateWithinDayService::class;
    }
}
