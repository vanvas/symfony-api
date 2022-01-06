<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class LongTextType implements SchemaTypeInterface
{
    public function __construct(
        public array $context = [],
    ) {
    }

    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_LONGTEXT;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
