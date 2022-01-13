<?php
declare(strict_types=1);

namespace Vim\Api\DTO;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vim\Api\Attribute\Schema\Type\ChoiceType;
use Vim\Api\Attribute\Schema\Type\RelationType;
use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class SchemaItem
{
    private string $type;
    
    private string $name;

    private ?array $values;

    private ?array $context;

    public function __construct(
        \ReflectionProperty $property,
        SchemaTypeInterface $attribute,
        private ?array $embedded,
        private ?string $listUrl,
    ) {
        $this->type = $attribute->getType();
        $this->name = $property->getName();
        $this->values = $attribute instanceof RelationType || $attribute instanceof ChoiceType ? $attribute->values : null;
        $this->context = $attribute->getContext();
    }
}
