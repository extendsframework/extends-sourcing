<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Command\Dispatcher\CommandDispatcherInterface;
use ExtendsFramework\Command\Handler\AbstractCommandHandler;
use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use PHPUnit\Framework\TestCase;

class CommandDispatcherFactoryTest extends TestCase
{
    /**
     * Create service.
     *
     * Test that correct service will be created.
     *
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\CommandDispatcherFactory::createService()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\CommandDispatcherFactory::getCommandHandler()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\CommandDispatcherFactory::getProxyCommandHandler()
     */
    public function testCreateService(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->method('getConfig')
            ->willReturn([
                CommandDispatcherInterface::class => [
                    EventSourcedAggregateStub::class => [
                        'FooBar',
                        'BarBaz',
                    ],
                    CommandHandlerStub::class => 'FooBar',
                ],
            ]);

        $serviceLocator
            ->method('getService')
            ->withConsecutive(
                [EventStoreInterface::class],
                [EventPublisherInterface::class],
                [EventSourcedAggregateStub::class],
                [CommandHandlerStub::class]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createMock(EventStoreInterface::class),
                $this->createMock(EventPublisherInterface::class),
                $this->createMock(EventSourcedAggregateStub::class),
                $this->createMock(CommandHandlerStub::class)
            );

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $factory = new CommandDispatcherFactory();
        $dispatcher = $factory->createService(CommandDispatcherInterface::class, $serviceLocator);

        $this->assertInstanceOf(CommandDispatcherInterface::class, $dispatcher);
    }
}

class EventSourcedAggregateStub extends EventSourcedAggregate
{
}

class CommandHandlerStub extends AbstractCommandHandler
{
}
