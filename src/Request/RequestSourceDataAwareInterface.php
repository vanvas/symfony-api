<?php
declare(strict_types=1);

namespace Vim\Api\Request;

interface RequestSourceDataAwareInterface
{
    public function getRequestSourceData(): array|string|null;

    public function getRequestSourceDataAsString(): ?string;

    public function setRequestSourceData(array|string|null $requestSourceData): void;
}
