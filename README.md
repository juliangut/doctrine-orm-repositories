[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-orm-repositories)
[![License](https://img.shields.io/github/license/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://github.com/juliangut/doctrine-orm-repositories/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://travis-ci.org/juliangut/doctrine-orm-repositories)
[![Style Check](https://styleci.io/repos/85766491/shield)](https://styleci.io/repos/85766491)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/doctrine-orm-repositories)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://coveralls.io/github/juliangut/doctrine-orm-repositories)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-orm-repositories)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/doctrine-orm-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-orm-repositories)

# doctrine-orm-repositories

Doctrine2 ORM utility entity repositories

## Installation

### Composer

```
composer require juliangut/doctrine-orm-repositories
```

## Usage

### Use repositoryClass on mapped classes

```php
/**
 * Comment entity.
 *
 * @ORM\Entity(repositoryClass="\Jgut\Doctrine\Repository\ORM\RelationalRepository")
 */
class Comment
{
}
```

### Register factory on managers

When creating object managers you can set a repository factory to create default repositories such as follows

```php
use Jgut\Doctrine\Repository\ORM\RelationalRepositoryFactory;

$config = new \Doctrine\ORM\Configuration;
$config->setRepositoryFactory(new RelationalRepositoryFactory);

$entityManager = \Doctrine\ORM\EntityManager::create([], $config);
```

> For an easier way of registering repository factories and managers generation in general have a look at [juliangut/doctrine-manager-builder](https://github.com/juliangut/doctrine-manager-builder)

## Functionalities

Head to [juliangut/doctrine-base-repositories](https://github.com/juliangut/doctrine-base-repositories) for a list of new methods provided by the repository

Additionally [Specification pattern](https://en.wikipedia.org/wiki/Specification_pattern) is supported thanks to [rikbruil/Doctrine-Specification](https://github.com/rikbruil/Doctrine-Specification)

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/doctrine-orm-repositories/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/doctrine-orm-repositories/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/doctrine-orm-repositories/blob/master/LICENSE) included with the source code for a copy of the license terms.
