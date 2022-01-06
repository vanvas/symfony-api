<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class CustomType implements SchemaInterface
{
    public function __construct(
        public string $type,
        public array $context = [],
    ) {}
    
    public function getType(): string
    {
        return $this->type;
    }
}
