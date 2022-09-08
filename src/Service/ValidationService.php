<?php
declare(strict_types=1);

namespace Vim\Api\Service;

use Doctrine\Common\Collections\Collection;
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
            $propertyPath = $this->preparePropertyPath($item->getPropertyPath(), $item->getRoot());
            $errors[$propertyPath][] = $item->getMessage();
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function preparePropertyPath(string $propertyPath, ?object $root): string
    {
        if (!$root) {
            return $propertyPath;
        }

        if (!\preg_match('/\[[0-9]+\]/', $propertyPath)) {
            return $propertyPath;
        }

        if (!\str_contains($propertyPath, '.')) {
            return $propertyPath;
        }

        $preparedSegments = [];
        $stateRelation = $root;
        $propertySegments = \explode('.', $propertyPath);
        foreach ($propertySegments as $propertySegmentIndex => $propertySegment) {
            if ($propertySegmentIndex === \count($propertySegments) - 1) {
                $preparedSegments[] = $propertySegment;

                continue;
            }
            
            preg_match('/(?<propertyName>[a-zA-Z]+)\[(?<propertyIndex>[0-9]+)\]/', $propertySegment, $matches);
            
            $ref = new \ReflectionObject($stateRelation);
            $refProperty = $ref->getProperty($matches['propertyName']);
            $refProperty->setAccessible(true);
            $stateRelation = $refProperty->getValue($stateRelation);
            
            if (!\array_key_exists('propertyIndex', $matches)) {
                $preparedSegments[] = $propertySegment;

                continue;
            }
            
            if (!$stateRelation instanceof Collection) {
                $preparedSegments[] = $propertySegment;
                
                continue;
            }

            $propertyNewIndex = \array_flip($stateRelation->getKeys())[(int) $matches['propertyIndex']];

            $preparedSegments[] = $matches['propertyName'] . '['.$propertyNewIndex.']';
        }

        return \implode('.', $preparedSegments);
    }
}
