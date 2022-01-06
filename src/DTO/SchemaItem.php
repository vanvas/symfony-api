<?php
declare(strict_types=1);

namespace Vim\Api\DTO;

use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class SchemaItem
{
    private string $type;
    
    private string $name;

    private array $context;
    
    public function __construct(
        \ReflectionProperty $property,
        SchemaTypeInterface $attribute,
        private ?array $embedded,
        private ?string $listUrl,
    ) {
        $this->type = $attribute->getType();
        $this->name = $property->getName();
        $this->context = $attribute->getContext();
    }
}
