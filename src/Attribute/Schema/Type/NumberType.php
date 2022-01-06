<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class NumberType implements SchemaTypeInterface
{
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_NUMBER;
    }
}
