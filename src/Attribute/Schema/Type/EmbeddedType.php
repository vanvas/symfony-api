<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class EmbeddedType implements SchemaTypeInterface
{
    public function __construct(
        public bool $multiple,
        public string $className,
        public ?string $name = null,
        public ?int $priority = null,
        public ?array $groups = null,
        public ?array $context = null,
    ) {
    }
    
    public function getType(): string
    {
        return SchemaTypeInterface::TYPE_EMBEDDED;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getGroups(): ?array
    {
        return $this->groups;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
