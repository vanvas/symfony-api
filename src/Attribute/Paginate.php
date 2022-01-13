<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
final class Paginate extends Collection
{
    public function __construct(
        public int $defaultPerPage = 10,
        public ?string $defaultSortBy = null,
        public bool $defaultOrderDesc = true,
    ) {
        parent::__construct($defaultSortBy, $defaultOrderDesc);
    }
}
