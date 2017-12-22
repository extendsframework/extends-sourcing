<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Loader;

use ExtendsFramework\ServiceLocator\Resolver\Factory\FactoryResolver;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository;
use ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\EventStoreRepositoryFactory;
use ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\MongoEventStoreFactory;
use ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore;
use PHPUnit\Framework\TestCase;

class SourcingConfigLoaderTest extends TestCase
{
    /**
     * Load.
     *
     * Test that correct config will be loaded.
     *
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Loader\SourcingConfigLoader::load()
     */
    public function testLoad(): void
    {
        $loader = new SourcingConfigLoader();

        $this->assertSame([
            ServiceLocatorInterface::class => [
                FactoryResolver::class => [
                    EventStoreRepository::class => EventStoreRepositoryFactory::class,
                    MongoEventStore::class => MongoEventStoreFactory::class,
                ],
            ],
        ], $loader->load());
    }
}
