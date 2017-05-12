<?php

/*
 * doctrine-orm-repositories (https://github.com/juliangut/doctrine-orm-repositories).
 * Doctrine2 ORM utility entity repositories.
 *
 * @license MIT
 * @link https://github.com/juliangut/doctrine-orm-repositories
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Doctrine\Repository\ORM;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as RelationalPaginator;
use Jgut\Doctrine\Repository\EventsTrait;
use Jgut\Doctrine\Repository\PaginatorTrait;
use Jgut\Doctrine\Repository\Repository;
use Jgut\Doctrine\Repository\RepositoryTrait;
use Rb\Specification\Doctrine\SpecificationAwareInterface;
use Rb\Specification\Doctrine\SpecificationRepositoryTrait;
use Zend\Paginator\Paginator;

/**
 * Relational entity repository.
 */
class RelationalRepository extends EntityRepository implements Repository, SpecificationAwareInterface
{
    use SpecificationRepositoryTrait;
    use RepositoryTrait;
    use EventsTrait;
    use PaginatorTrait;

    /**
     * Class alias.
     *
     * @var string
     */
    protected $classAlias;

    /**
     * Get class alias.
     *
     * @return string
     */
    protected function getClassAlias(): string
    {
        if ($this->classAlias === null) {
            $this->classAlias = strtoupper($this->getEntityName()[0]);
        }

        return $this->classAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return ClassUtils::getRealClass(parent::getEntityName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager(): EntityManager
    {
        return $this->getEntityManager();
    }

    /**
     * {@inheritdoc}
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int        $itemsPerPage
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = [], int $itemsPerPage = 10): Paginator
    {
        $queryBuilder = $this->getQueryBuilderFromCriteria($criteria, $orderBy);

        return $this->paginate($queryBuilder->getQuery(), $itemsPerPage);
    }

    /**
     * Get query builder from criteria array.
     *
     * @param array $criteria
     * @param array $orderBy
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilderFromCriteria(array $criteria, array $orderBy = []): QueryBuilder
    {
        $entityAlias = $this->getClassAlias();
        $queryBuilder = $this->createQueryBuilder($entityAlias);

        foreach ($criteria as $field => $value) {
            if (is_null($value)) {
                $queryBuilder->andWhere(sprintf('%s.%s IS NULL', $entityAlias, $field));
            } else {
                $parameter = sprintf('%s_%s', $field, substr(sha1($field), 0, 4));

                $queryBuilder->andWhere(sprintf('%s.%s = :%s', $entityAlias, $field, $parameter));
                $queryBuilder->setParameter($parameter, $value);
            }
        }

        if (is_array($orderBy)) {
            $entityAlias = count($queryBuilder->getRootAliases())
                ? $queryBuilder->getRootAliases()[0]
                : $this->getClassAlias();

            foreach ($orderBy as $field => $order) {
                $queryBuilder->addOrderBy($entityAlias . '.' . $field, $order);
            }
        }

        return $queryBuilder;
    }

    /**
     * Paginate query.
     *
     * @param Query|QueryBuilder $query
     * @param int                $itemsPerPage
     *
     * @throws \InvalidArgumentException
     *
     * @return Paginator
     */
    protected function paginate($query, int $itemsPerPage = 10): Paginator
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        if (!$query instanceof Query) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Query must be a Query or QueryBuilder object. "%s" given',
                    is_object($query) ? get_class($query) : gettype($query)
                )
            );
        }

        return $this->getPaginator(new RelationalPaginatorAdapter(new RelationalPaginator($query)), $itemsPerPage);
    }

    /**
     * {@inheritdoc}
     *
     * @param array|QueryBuilder $criteria
     *
     * @return int
     */
    public function countBy($criteria): int
    {
        return $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->getEntityName())->count($criteria);
    }
}
