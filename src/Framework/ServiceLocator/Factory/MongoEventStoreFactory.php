<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\ServiceFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore;
use MongoDB\Driver\Manager;

class MongoEventStoreFactory implements ServiceFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): EventStoreInterface
    {
        $config = $serviceLocator->getConfig();
        $config = $config[MongoEventStore::class] ?? [];

        return new MongoEventStore(
            $this->getManager($serviceLocator),
            $config['namespace'],
            $this->getSerializer($serviceLocator)
        );
    }

    /**
     * Get MongoDB manager.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Manager
     * @throws ServiceLocatorException
     */
    protected function getManager(ServiceLocatorInterface $serviceLocator): Manager
    {
        return $serviceLocator->getService(Manager::class);
    }

    /**
     * Get object serializer for payload.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SerializerInterface
     * @throws ServiceLocatorException
     */
    protected function getSerializer(ServiceLocatorInterface $serviceLocator): SerializerInterface
    {
        return $serviceLocator->getService(SerializerInterface::class);
    }
}
