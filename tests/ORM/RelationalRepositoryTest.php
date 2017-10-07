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

namespace Jgut\Doctrine\Repository\ORM\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Jgut\Doctrine\Repository\ORM\RelationalRepository;
use Jgut\Doctrine\Repository\ORM\Tests\Stubs\EntityStub;
use Jgut\Doctrine\Repository\ORM\Tests\Stubs\RepositoryStub;
use PHPUnit\Framework\TestCase;
use Zend\Paginator\Paginator;

/**
 * Relational repository tests.
 */
class RelationalRepositoryTest extends TestCase
{
    public function testEntityName()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityStub::class));

        static::assertEquals(EntityStub::class, $repository->getClassName());
    }

    public function testFilterCollection()
    {
        $filterCollection = $this->getMockBuilder(FilterCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var FilterCollection $filterCollection */

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getFilters')
            ->will(static::returnValue($filterCollection));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, new ClassMetadata(EntityStub::class));

        static::assertSame($filterCollection, $repository->getFilterCollection());
    }

    public function testEntityManager()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, new ClassMetadata(EntityStub::class));

        static::assertSame($manager, $repository->getManager());
    }

    public function testFindPaginated()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects(static::any())
            ->method('getDefaultQueryHints')
            ->will(static::returnValue([]));
        $configuration->expects(static::once())
            ->method('isSecondLevelCacheEnabled')
            ->will(static::returnValue(false));
        /* @var Configuration $configuration */

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getConfiguration')
            ->will(static::returnValue($configuration));

        $query = new Query($manager);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->expects(static::once())
            ->method('select')
            ->will(static::returnSelf());
        $queryBuilder->expects(static::once())
            ->method('from')
            ->will(static::returnSelf());
        $queryBuilder->expects(static::once())
            ->method('getQuery')
            ->will(static::returnValue($query));
        /* @var QueryBuilder $queryBuilder */

        $manager->expects(static::once())
            ->method('createQueryBuilder')
            ->will(static::returnValue($queryBuilder));
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, new ClassMetadata(EntityStub::class));

        $paginator = $repository->findPaginatedBy(
            [
                'fieldOne' => null,
                'fieldTwo' => 1,
                'fieldThree' => ['a', 'b', 'c'],
            ],
            ['fakeField' => 'ASC']
        );

        static::assertInstanceOf(Paginator::class, $paginator);
    }

    public function testCount()
    {
        $persister = $this->getMockBuilder(BasicEntityPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $persister->expects(static::once())
            ->method('count')
            ->will(static::returnValue(10));
        /* @var BasicEntityPersister $persister */

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uow->expects(static::once())
            ->method('getEntityPersister')
            ->will(static::returnValue($persister));
        /* @var UnitOfWork $uow */

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::once())
            ->method('getUnitOfWork')
            ->will(static::returnValue($uow));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityStub::class));

        static::assertEquals(10, $repository->countBy([]));
    }

    public function testTransactions()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects(static::exactly(2))
            ->method('beginTransaction');
        $connection->expects(static::once())
            ->method('commit');
        $connection->expects(static::once())
            ->method('rollBack');
        /* @var Connection $connection */

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getConnection')
            ->will(static::returnValue($connection));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityStub::class));

        $repository->beginTransaction();
        $repository->commit();

        $repository->beginTransaction();
        $repository->rollBack();
    }
}
