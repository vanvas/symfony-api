<?php
declare(strict_types=1);

namespace Vim\Api\Service\QueryFilter;

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
        bool $orderAsc = false
    ): Query {
        $rootAlias = $this->entityService->getAlias($entityName) . '_';
        $qb = $this->entityService->getRepository($entityName)->createQueryBuilder($rootAlias);

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            if (null === ($value = $data[$filter->getRequestParam()] ?? null)) {
                continue;
            }

            $field = $rootAlias . '.' . $filter->getDbParam();
            $relations = explode('.', $filter->getDbParam());
            if (count($relations) > 1) {
                $field = implode('.', array_slice($relations, -2));
                $joinAlias = $rootAlias;
                foreach ($relations as $key => $relation) {
                    if ($key !== count($relations) - 1 && !in_array($relation, $qb->getAllAliases())) {
                        $qb->innerJoin($joinAlias . '.' . $relation, $relation);
                        $joinAlias = $relation;
                    }
                }
            }

            $paramKey = 'param_' . md5(uniqid((string) rand(100000, 9999999)));
            $this->filterServiceCollection
                ->getByName($filter->getService())
                ->prepareQuery($filter, $qb, $field, $value, $paramKey)
            ;
        }

        $qb->addGroupBy($rootAlias . '.' . $this->entityService->getIdentifierName($entityName));

        if ($sortBy) {
            $sortBy = str_contains($sortBy, '.') ? $sortBy : $rootAlias . '.' . $sortBy;
        } else {
            $sortBy = $rootAlias . '.' . $this->entityService->getIdentifierName($entityName);
        }

        $qb->orderBy($sortBy, $orderAsc ? 'ASC' : 'DESC');

        return $qb->getQuery();
    }
}
