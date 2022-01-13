<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute]
final class ChoiceType implements SchemaTypeInterface
{
    public function __construct(
        public bool $multiple,
        public ?string $routeName = null,
        public array $routeParameters = [],
        public ?array $values = null,
        public ?array $context = null,
    ) {
    }
    
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_CHOICE;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
