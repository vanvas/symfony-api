<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class LongTextType implements SchemaTypeInterface
{
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_LONGTEXT;
    }
}
