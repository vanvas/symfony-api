<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class CustomType implements SchemaTypeInterface
{
    public function __construct(
        public string $type,
        public ?array $context = null,
    ) {}
    
    public function getType(): string
    {
        return $this->type;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
