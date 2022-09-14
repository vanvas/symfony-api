<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter;

use Vim\Api\Attribute\Filter\DefaultFilterInterface;
use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Service\EntityService;
use Doctrine\ORM\Query;

class QueryService
{
    public function __construct(
        private EntityService $entityService,
        private FilterServiceCollection $filterServiceCollection
    ) {}

    public function getGlobalQuery(
        string $entityName,
        array $data = [],
        array $filters = [],
        string $sortBy = null,
        bool $orderDesc = true
    ): Query {
        $rootAlias = $this->entityService->getAlias($entityName) . '_';
        $qb = $this->entityService->getRepository($entityName)->createQueryBuilder($rootAlias);

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            $value = $data[$filter->getRequestParam()] ?? null;
            if (null === $value && !$filter instanceof DefaultFilterInterface) {
                continue;
            }

            $fieldMap = [];
            $dbParams = \array_map('trim', \explode(',', $filter->getDbParam()));
            foreach ($dbParams as $dbParam) {
                $relationNames = \array_map(
                    fn (string $relationName) => $this->prepareJoinAlias($relationName),
                    $this->getEntityNamesTree($dbParam, $entityName)
                );
                \array_shift($relationNames);
                if (!$relationNames) {
                    $fieldMap[$dbParam] = $rootAlias . '.' . $dbParam;
                    continue;
                }

                $relationProperties = \explode('.', $dbParam);
                $fieldMap[$dbParam] = $relationNames[\count($relationNames) - 1] . '.' . \array_pop($relationProperties);

                foreach ($relationProperties as $index => $relationProperty) {
                    if (!$relationName = $relationNames[$index] ?? null) {
                        break;
                    }

                    if (\in_array($relationName, $qb->getAllAliases())) {
                        continue;
                    }

                    $join = (0 === $index ? $rootAlias : $relationNames[$index - 1]) . '.' . $relationProperty;
                    $qb->innerJoin($join, $relationName);
                }
            }

            $paramKey = 'param_' . md5(uniqid((string) rand(100000, 9999999)));
            $this->filterServiceCollection
                ->getByName($filter->getService())
                ->prepareQuery($filter, $qb, \array_values($fieldMap), $value, $paramKey)
            ;
        }

        $qb->addGroupBy($rootAlias . '.' . $this->entityService->getIdentifierName($entityName));

        if ($sortBy) {
            $sortBy = str_contains($sortBy, '.') ? $sortBy : $rootAlias . '.' . $sortBy;
        } else {
            $sortBy = $rootAlias . '.' . $this->entityService->getIdentifierName($entityName);
        }

        $qb->orderBy($sortBy, $orderDesc ? 'DESC' : 'ASC');

        return $qb->getQuery();
    }

    private function getEntityNamesTree(string $path, string $sourceEntityName, array $initial = []): array
    {
        $metadata = $this->entityService->getMetaData($sourceEntityName);
        $exception = new \Exception(\sprintf('The path "%s" is not valid or not configured for the "%s"', $path, $sourceEntityName));
        if (!\str_contains($path, '.')) {
            return \array_key_exists($path, $metadata->fieldMappings) ? [...$initial, $sourceEntityName] : throw $exception;
        }

        $segments = \explode('.', $path);
        $entityName = $metadata->associationMappings[\array_shift($segments)]['targetEntity'] ?? null;
        if (!$entityName) {
            throw $exception;
        }

        return $this->getEntityNamesTree(\implode('.', $segments), $entityName, [...$initial, $sourceEntityName]);
    }

    private function prepareJoinAlias(string $origin): string
    {
        return \preg_replace('/[^a-zA-Z]+/','',$origin);
    }
}
