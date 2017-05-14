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

use Doctrine\ORM\EntityManager;
use Jgut\Doctrine\Repository\ORM\RelationalRepository;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Paginator;

/**
 * Repository stub.
 */
class RepositoryStub extends RelationalRepository
{
    /**
     * @return EntityManager
     */
    public function getManager(): EntityManager
    {
        return parent::getManager();
    }

    /**
     * @param AdapterInterface $adapter
     * @param int              $itemsPerPage
     *
     * @return Paginator
     */
    protected function getPaginator(AdapterInterface $adapter, int $itemsPerPage): Paginator
    {
        return new Paginator($adapter);
    }
}
