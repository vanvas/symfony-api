<?php
declare(strict_types=1);

namespace Vim\Api\Request;

trait RequestSourceDataAwareTrait
{
    private string|array $requestSourceData;

    public function getRequestSourceData(): array|string
    {
        return $this->requestSourceData;
    }

    public function setRequestSourceData(array|string $requestSourceData): void
    {
        $this->requestSourceData = $requestSourceData;
    }

    public function getRequestSourceDataAsString(): string
    {
        return \is_array($this->getRequestSourceData())
            ? \json_encode($this->getRequestSourceData())
            : $this->getRequestSourceData();
    }
}
