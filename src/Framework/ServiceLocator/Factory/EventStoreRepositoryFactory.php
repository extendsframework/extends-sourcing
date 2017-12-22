<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\ServiceFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;

class EventStoreRepositoryFactory implements ServiceFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): EventStoreRepository
    {
        return new EventStoreRepository(
            $this->getEventStore($serviceLocator),
            $this->getEventPublisher($serviceLocator),
            $extra['aggregateClass']
        );
    }

    /**
     * Get event store.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return EventStoreInterface
     * @throws ServiceLocatorException
     */
    protected function getEventStore(ServiceLocatorInterface $serviceLocator): EventStoreInterface
    {
        return $serviceLocator->getService(EventStoreInterface::class);
    }

    /**
     * Get event publisher.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return EventPublisherInterface
     * @throws ServiceLocatorException
     */
    protected function getEventPublisher(ServiceLocatorInterface $serviceLocator): EventPublisherInterface
    {
        return $serviceLocator->getService(EventPublisherInterface::class);
    }
}
