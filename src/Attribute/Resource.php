<?php
declare(strict_types=1);

namespace Vim\Api\Attribute;

#[\Attribute]
final class Resource
{
    public function __construct(public string $entity) {}
}
