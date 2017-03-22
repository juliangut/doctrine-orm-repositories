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

namespace Jgut\Doctrine\Repository\ORM\Tests\Stubs;

use Jgut\Doctrine\Repository\ORM\RelationalRepository;

/**
 * Repository stub.
 */
class RepositoryStub extends RelationalRepository
{
    public function getManager()
    {
        return parent::getManager();
    }
}
