<?php
declare(strict_types=1);

namespace Vim\Api\Request;

use JMS\Serializer\Annotation as JMS;

trait RequestSourceDataAwareTrait
{
    #[JMS\Exclude]
    private string|array|null $requestSourceData = null;

    public function getRequestSourceData(): array|string|null
    {
        return $this->requestSourceData;
    }

    public function setRequestSourceData(array|string|null $requestSourceData): void
    {
        $this->requestSourceData = $requestSourceData;
    }

    public function getRequestSourceDataAsString(): ?string
    {
        return \is_array($this->getRequestSourceData())
            ? \json_encode($this->getRequestSourceData())
            : $this->getRequestSourceData();
    }
}
