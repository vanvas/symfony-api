<?php

namespace Vim\Api\Attribute\Hydration;

#[\Attribute]
class Identity
{
    private string|array $propertyName;

    public function __construct(string|array $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string|array
    {
        return $this->propertyName;
    }

    public function getPropertyNames(): array
    {
        return \is_array($this->propertyName) ? $this->propertyName : [$this->propertyName];
    }
}
