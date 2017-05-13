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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\ORM\UnitOfWork;
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
}
