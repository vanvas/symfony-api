<?php
declare(strict_types=1);

namespace Vim\Api\Exception;

use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;

class SchemaAttributeNotSetException extends \Exception implements ExceptionInterface
{
    public function __construct(\ReflectionProperty $property)
    {
        parent::__construct('You need to provide attribute "'.SchemaTypeInterface::class.'" for the "'.$property->getName().'"');
    }
}
