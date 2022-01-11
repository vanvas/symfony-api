<?php
declare(strict_types=1);

namespace Vim\Api\Exception;

use Vim\Api\DTO\FilterItem;

class FilterRouteException extends \Exception implements ExceptionInterface
{
    public function __construct(private array $filters)
    {
        parent::__construct();
    }

    /**
     * @return FilterItem[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
