<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Loader;

use ExtendsFramework\ServiceLocator\Config\Loader\LoaderInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\FactoryResolver;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository;
use ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\EventStoreRepositoryFactory;
use ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\MongoEventStoreFactory;
use ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore;

class SourcingConfigLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(): array
    {
        return [
            ServiceLocatorInterface::class => [
                FactoryResolver::class => [
                    EventStoreRepository::class => EventStoreRepositoryFactory::class,
                    MongoEventStore::class => MongoEventStoreFactory::class,
                ],
            ],
        ];
    }
}
