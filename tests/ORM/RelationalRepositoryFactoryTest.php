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
use Jgut\Doctrine\Repository\ORM\RelationalRepository;
use Jgut\Doctrine\Repository\ORM\RelationalRepositoryFactory;
use PHPUnit\Framework\TestCase;

/**
 * Relational repository factory tests.
 */
class RelationalRepositoryFactoryTest extends TestCase
{
    public function testCount()
    {
        $classMetadata = new ClassMetadata('RepositoryEntity');

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(static::any())
            ->method('getClassMetadata')
            ->will(static::returnValue($classMetadata));
        /* @var EntityManager $manager */

        $factory = new RelationalRepositoryFactory();

        $repository = $factory->getRepository($manager, 'RepositoryEntity');

        static::assertInstanceOf(RelationalRepository::class, $repository);
        static::assertEquals($repository, $factory->getRepository($manager, 'RepositoryEntity'));
    }
}
