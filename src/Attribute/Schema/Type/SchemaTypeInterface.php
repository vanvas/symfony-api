<?php

declare(strict_types=1);

namespace Vim\Api\Attribute\Schema\Type;

interface SchemaTypeInterface
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_EMBEDDED = 'embedded';
    public const TYPE_RELATION = 'relation';
    public const TYPE_CHOICE = 'choice';
    public const TYPE_STRING = 'string';
    public const TYPE_TEXT = 'text';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_LONGTEXT = 'longtext';
    public const TYPE_NUMBER = 'number';
    
    public function getType(): string;

    public function getContext(): ?array;
}
