<?php
declare(strict_types=1);

namespace Vim\Api\Exception;

class ValidationException extends \Exception implements ExceptionInterface
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed.', ExceptionInterface::VALIDATION_ERROR);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
