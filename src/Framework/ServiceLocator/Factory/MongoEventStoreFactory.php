<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\ServiceFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore;
use MongoDB\Driver\Manager;

class MongoEventStoreFactory implements ServiceFactoryInterface
{
    /**
     * @inheritDoc
     * @throws ServiceLocatorException
     */
    public function createService(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        $config = $serviceLocator->getConfig();
        $config = $config[MongoEventStore::class] ?? [];

        /** @noinspection PhpParamsInspection */
        return new MongoEventStore(
            $serviceLocator->getService(Manager::class),
            $config['namespace'],
            $serviceLocator->getService(SerializerInterface::class)
        );
    }
}
