<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use PHPUnit\Framework\TestCase;

class EventStoreRepositoryFactoryTest extends TestCase
{
    /**
     * Create service.
     *
     * Test that correct service will be created.
     *
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\EventStoreRepositoryFactory::createService()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\EventStoreRepositoryFactory::getEventPublisher()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\EventStoreRepositoryFactory::getEventStore()
     */
    public function testCreateService(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->method('getService')
            ->withConsecutive(
                [EventStoreInterface::class],
                [EventPublisherInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createMock(EventStoreInterface::class),
                $this->createMock(EventPublisherInterface::class)
            );

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $factory = new EventStoreRepositoryFactory();
        $eventStore = $factory->createService(EventStoreRepository::class, $serviceLocator, [
            'aggregateClass' => 'SomeFancyAggregate',
        ]);

        $this->assertInstanceOf(EventStoreRepository::class, $eventStore);
    }
}
