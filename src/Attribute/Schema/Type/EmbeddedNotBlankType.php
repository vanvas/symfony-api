<?php
declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class EmbeddedNotBlankType extends EmbeddedType
{
    private const CONTEXT_NOT_BLANK = 'notBlank';
    private const CONTEXT_TARGET_NAME = 'targetName';

    public function __construct(
        public string $className,
        private string $targetName,
        public ?string $routeName = null,
        public array $routeParameters = [],
        public ?array $values = null,
        public ?string $name = null,
        public ?int $priority = null,
        public ?array $groups = null,
        public ?array $context = null,
    ) {
        parent::__construct(true, $className, $name, $priority, $groups, $context);
    }

    public function getContext(): array
    {
        $context = [
            self::CONTEXT_NOT_BLANK => true,
            self::CONTEXT_TARGET_NAME => $this->targetName,
        ];

        return [...$context, ...($this->context ?? [])];
    }
}
