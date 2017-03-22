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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Jgut\Doctrine\Repository\ORM\RelationalRepository;
use Jgut\Doctrine\Repository\ORM\Tests\Stubs\EntityStub;
use Jgut\Doctrine\Repository\ORM\Tests\Stubs\RepositoryStub;
use PHPUnit\Framework\TestCase;

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

    public function testEntityManager()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RepositoryStub($manager, new ClassMetadata(EntityStub::class));

        static::assertSame($manager, $repository->getManager());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Criteria must be an array of query fields or a Doctrine\ORM\QueryBuilder
     */
    public function testInvalidCriteria()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityStub::class));

        $repository->findPaginatedBy('');
    }

    public function testCount()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects(static::exactly(2))
            ->method('getSingleScalarResult')
            ->will(static::returnValue(10));

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$manager])
            ->setMethodsExcept(['select', 'from', 'andWhere', 'setParameter', 'add'])
            ->getMock();
        $queryBuilder->expects(static::exactly(2))
            ->method('getQuery')
            ->will(static::returnValue($query));
        /* @var QueryBuilder $queryBuilder */

        $manager->expects(static::once())
            ->method('createQueryBuilder')
            ->will(static::returnValue($queryBuilder));
        /* @var EntityManager $manager */

        $repository = new RelationalRepository($manager, new ClassMetadata(EntityStub::class));

        static::assertEquals(10, $repository->countBy($queryBuilder));

        $queryBuilder->expects(static::exactly(2))
            ->method('getRootAliases')
            ->will(static::returnValue(['a']));

        static::assertEquals(10, $repository->countBy(['fakeField' => 'fakeValue', 'nullFakeField' => null]));
    }
}
