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
use Happyr\DoctrineSpecification\EntitySpecificationRepositoryInterface;
use Happyr\DoctrineSpecification\EntitySpecificationRepositoryTrait;
use Jgut\Doctrine\Repository\EventsTrait;
use Jgut\Doctrine\Repository\PaginatorTrait;
use Jgut\Doctrine\Repository\Repository;
use Jgut\Doctrine\Repository\RepositoryTrait;
use Zend\Paginator\Paginator;

/**
 * Relational entity repository.
 */
class RelationalRepository extends EntityRepository implements Repository, EntitySpecificationRepositoryInterface
{
    use RepositoryTrait;
    use EventsTrait;
    use PaginatorTrait;
    use EntitySpecificationRepositoryTrait;

    /**
     * Class name.
     *
     * @var string
     */
    protected $className;

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
        if ($this->className === null) {
            $this->className = ClassUtils::getRealClass($this->getEntityName());
        }

        return $this->className;
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
        $entityAlias = count($queryBuilder->getRootAliases()) ? $queryBuilder->getRootAliases()[0] : $entityAlias;

        foreach ($criteria as $field => $value) {
            $this->addQueryCriteria($queryBuilder, $field, $value, $entityAlias);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder->addOrderBy($entityAlias . '.' . $field, $order);
            }
        }

        return $queryBuilder;
    }

    /**
     * Add query builder criteria.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     * @param mixed        $value
     * @param string       $entityAlias
     */
    protected function addQueryCriteria(QueryBuilder $queryBuilder, string $field, $value, string $entityAlias)
    {
        if ($value === null) {
            $queryBuilder->andWhere(sprintf('%s.%s IS NULL', $entityAlias, $field));
        } else {
            $placeholder = sprintf('%s_%s', $field, substr(sha1($field), 0, 5));

            if (is_array($value)) {
                $queryBuilder->andWhere(sprintf('%s.%s IN (:%s)', $entityAlias, $field, $placeholder));
            } else {
                $queryBuilder->andWhere(sprintf('%s.%s = :%s', $entityAlias, $field, $placeholder));
            }

            $queryBuilder->setParameter($placeholder, $value);
        }
    }

    /**
     * Paginate query.
     *
     * @param Query $query
     * @param int   $itemsPerPage
     *
     * @return Paginator
     */
    protected function paginate(Query $query, int $itemsPerPage = 10): Paginator
    {
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

    /**
     * Begin transaction.
     */
    public function beginTransaction()
    {
        $this->getManager()->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit()
    {
        $this->getManager()->getConnection()->commit();
    }

    /**
     * Rollback transaction.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollBack()
    {
        $this->getManager()->getConnection()->rollBack();
    }
}
