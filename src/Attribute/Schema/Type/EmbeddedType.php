<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class EmbeddedType implements SchemaTypeInterface
{
    public function __construct(
        public bool $multiple,
        public string $className,
        public ?array $context = null,
    ) {
    }
    
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_EMBEDDED;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
