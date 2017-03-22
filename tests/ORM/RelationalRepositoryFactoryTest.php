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

namespace Jgut\Doctrine\Repository\ORM\Tests\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Jgut\Doctrine\Repository\ORM\RelationalRepositoryFactory;
use Jgut\Doctrine\Repository\RelationalRepository;

/**
 * Relational repository factory tests.
 *
 * @group relational
 */
class RelationalRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
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
