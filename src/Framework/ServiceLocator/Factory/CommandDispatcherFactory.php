<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Command\Framework\ServiceLocator\Factory;
use ExtendsFramework\Command\Handler\CommandHandlerInterface;
use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregateInterface;
use ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;

class CommandDispatcherFactory extends Factory\CommandDispatcherFactory
{
    /**
     * @inheritDoc
     */
    protected function getCommandHandler(ServiceLocatorInterface $serviceLocator, string $key): object
    {
        if (is_subclass_of($key, EventSourcedAggregateInterface::class, true)) {
            return $this->getProxyCommandHandler($serviceLocator, $key);
        }

        return parent::getCommandHandler($serviceLocator, $key);
    }

    /**
     * Get proxy command handler for aggregate.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $key
     * @return CommandHandlerInterface
     * @throws ServiceLocatorException
     */
    private function getProxyCommandHandler(ServiceLocatorInterface $serviceLocator, string $key): object
    {
        /** @noinspection PhpParamsInspection */
        return new ProxyCommandHandler(
            new EventStoreRepository(
                $serviceLocator->getService(EventStoreInterface::class),
                $serviceLocator->getService(EventPublisherInterface::class),
                $serviceLocator->getService($key)
            )
        );
    }
}
