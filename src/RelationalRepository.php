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
        return ClassUtils::getRealClass(parent::getClassName());
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
     * @param array|QueryBuilder $criteria
     * @param array|null         $orderBy
     * @param int                $itemsPerPage
     *
     * @throws \InvalidArgumentException
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginatedBy($criteria, array $orderBy = [], int $itemsPerPage = 10): Paginator
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);
        $entityAlias = count($queryBuilder->getRootAliases())
            ? $queryBuilder->getRootAliases()[0]
            : $this->getClassAlias();

        if (is_array($orderBy)) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder->addOrderBy($entityAlias . '.' . $field, $order);
            }
        }

        $adapter = new RelationalPaginatorAdapter(new RelationalPaginator($queryBuilder->getQuery()));

        return $this->getPaginator($adapter, $itemsPerPage);
    }

    /**
     * {@inheritdoc}
     *
     * @param array|QueryBuilder $criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function countBy($criteria): int
    {
        $queryBuilder = $this->createQueryBuilderFromCriteria($criteria);
        $entityAlias = count($queryBuilder->getRootAliases())
            ? $queryBuilder->getRootAliases()[0]
            : $this->getClassAlias();

        return (int) $queryBuilder
            ->select('COUNT(' . $entityAlias . ')')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Create query builder based on provided simple criteria.
     *
     * @param array|QueryBuilder $criteria
     *
     * @throws \InvalidArgumentException
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilderFromCriteria($criteria): QueryBuilder
    {
        if ($criteria instanceof QueryBuilder) {
            return $criteria;
        } elseif (!is_array($criteria)) {
            throw new \InvalidArgumentException(sprintf(
                'Criteria must be an array of query fields or a %s',
                QueryBuilder::class
            ));
        }

        $entityAlias = $this->getClassAlias();
        $queryBuilder = $this->createQueryBuilder($entityAlias);

        /* @var array $criteria */
        foreach ($criteria as $field => $value) {
            if (is_null($value)) {
                $queryBuilder->andWhere(sprintf('%s.%s IS NULL', $entityAlias, $field));
            } else {
                $parameter = sprintf('%s_%s', $field, substr(sha1($field), 0, 4));

                $queryBuilder->andWhere(sprintf('%s.%s = :%s', $entityAlias, $field, $parameter));
                $queryBuilder->setParameter($parameter, $value);
            }
        }

        return $queryBuilder;
    }
}
