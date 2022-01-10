<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class ArrayType implements SchemaTypeInterface
{
    public function __construct(
        public ?array $context = null,
    ) {
    }

    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_ARRAY;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
