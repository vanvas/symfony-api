<?php
declare(strict_types=1);

namespace Vim\Api\Exception;

interface ExceptionInterface
{
    public const VALIDATION_ERROR = 422;
    public const NOT_FOUND = 404;
    public const UNDEFINED_ERROR = 500;
}
