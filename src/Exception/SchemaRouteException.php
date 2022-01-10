<?php
declare(strict_types=1);

namespace Vim\Api\Exception;

use Vim\Api\Attribute\Schema\Schema;
use Vim\Api\Attribute\Schema\Type\SchemaTypeInterface;

class SchemaRouteException extends \Exception implements ExceptionInterface
{
    public function __construct(private Schema $schema, private array $groups)
    {
        parent::__construct();
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}
