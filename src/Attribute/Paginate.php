<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
final class Paginate extends Collection
{
    public int $perPage = 10;
}
