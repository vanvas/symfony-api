<?php
declare(strict_types=1);

namespace Vim\Api\Service;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vim\Api\Exception\ValidationException;

class ValidationService
{
    public function __construct(private ValidatorInterface $validator) {}

    /**
     * @param object $object
     * @throws ValidationException
     */
    public function validateObject(object $object): void
    {
        $errors = [];
        /** @var ConstraintViolation $item */
        foreach ($this->validator->validate($object) as $item) {
            $errors[$item->getPropertyPath()][] = $item->getMessage();
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
