<?php
declare(strict_types=1);

namespace Vim\Api\DTO;

use Vim\Api\Attribute\Filter\DateFrom;
use Vim\Api\Attribute\Filter\DateTo;
use Vim\Api\Attribute\Filter\DateWithinDay;
use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Filter\Like;
use Vim\Api\Attribute\Filter\MultiSelect;
use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;

class FilterItem
{
    public const CONTEXT_TYPE = 'type';
    public const CONTEXT_MULTIPLE = 'multiple';

    private string $type;

    private string $name;

    private ?array $context;

    private ?array $values;

    private bool $multiple;

    public function __construct(
        FilterInterface $attribute,
        private ?string $listUrl,
    ) {
        if ($attribute->getRouteName() && $attribute->getValues()) {
            throw new \LogicException('Only "route" or "values" must be set');
        }

        $context = $attribute->getContext() ?? [];

        if (!$type = $context[self::CONTEXT_TYPE] ?? null) {
            $type = $attribute->getRouteName() ? SchemaTypeInterface::TYPE_RELATION : match (get_class($attribute)) {
                DateFrom::class, DateTo::class, DateWithinDay::class => SchemaTypeInterface::TYPE_DATE,
                Like::class => SchemaTypeInterface::TYPE_STRING,
                MultiSelect::class => SchemaTypeInterface::TYPE_RELATION,
                default => SchemaTypeInterface::TYPE_STRING,
            };
        }

        $this->type = $type;
        $this->name = $attribute->getRequestParam();
        $this->values = $attribute->getValues();
        $this->context = $context;
        $this->multiple = get_class($attribute) === MultiSelect::class || true === ($context[self::CONTEXT_MULTIPLE] ?? null);
    }
}
