<?php

namespace Vim\Api\Attribute\Hydration;

#[\Attribute]
class Identity
{
    private string $propertyName;

    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
