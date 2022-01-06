<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class RelationType implements SchemaTypeInterface
{
    public function __construct(
        public bool $multiple,
        public ?string $routeName = null,
        public array $routeParameters = [],
        public ?array $context = null,
    ) {
    }
    
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_RELATION;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
