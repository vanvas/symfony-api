<?php
declare(strict_types=1);

namespace Vim\Api\DTO;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vim\Api\Attribute\Schema\Type\ChoiceType;
use Vim\Api\Attribute\Schema\Type\EmbeddedType;
use Vim\Api\Attribute\Schema\Type\RelationType;
use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Vim\Api\Exception\LogicException;

class SchemaItem
{
    private string $type;
    
    private string $name;

    private ?array $values;

    private ?array $context;

    private ?bool $multiple;

    private ?int $priority;

    public function __construct(
        \ReflectionProperty|\ReflectionClass|\ReflectionMethod $subject,
        SchemaTypeInterface $attribute,
        private ?array $embedded,
        private ?string $listUrl,
    ) {
        $this->name = $attribute->getName() ?? ($subject instanceof \ReflectionProperty ? $subject->getName() : null);
        if (!$this->name) {
            throw new LogicException('"name" should be set for the ' . print_r($attribute, true));
        }

        $this->type = $attribute->getType();
        $this->values = $attribute instanceof RelationType || $attribute instanceof ChoiceType ? $attribute->values : null;
        $this->priority = $attribute->getPriority();
        $this->context = $attribute->getContext();
        $this->multiple = $attribute instanceof RelationType || $attribute instanceof ChoiceType || $attribute instanceof EmbeddedType ? $attribute->multiple : null;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
