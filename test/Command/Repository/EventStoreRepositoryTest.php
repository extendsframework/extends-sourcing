<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Repository;

use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregateInterface;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use PHPUnit\Framework\TestCase;

class EventStoreRepositoryTest extends TestCase
{
    /**
     * Load.
     *
     * Test that stream will be load for identifier and the aggregate will be constructed.
     *
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::__construct()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::loadStream()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::getAggregate()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::getEventStore()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::getEventPublisher()
     */
    public function testLoad(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore
            ->expects($this->once())
            ->method('loadStream')
            ->with('foo')
            ->willReturn($stream);

        $eventPublisher = $this->createMock(EventPublisherInterface::class);

        /**
         * @var EventStoreInterface     $eventStore
         * @var EventPublisherInterface $eventPublisher
         */
        $repository = new EventStoreRepository($eventStore, $eventPublisher, EventSourcedAggregateStub::class);
        $aggregate = $repository->load('foo');

        $this->assertInstanceOf(EventSourcedAggregateStub::class, $aggregate);
    }

    /**
     * Save.
     *
     * Test that aggregate stream will be saved to event store and published.
     *
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::__construct()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::saveStream()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::publishStream()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::getEventStore()
     * @covers \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::getEventPublisher()
     */
    public function testSave(): void
    {
        $domainEventMessage = $this->createMock(DomainEventMessageInterface::class);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects($this->at(0))
            ->method('rewind');

        $stream
            ->expects($this->at(1))
            ->method('valid')
            ->willReturn(true);

        $stream
            ->expects($this->at(2))
            ->method('current')
            ->willReturn($domainEventMessage);

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore
            ->expects($this->once())
            ->method('saveStream')
            ->with($stream);

        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($domainEventMessage);

        $aggregate = $this->createMock(EventSourcedAggregateInterface::class);
        $aggregate
            ->method('getStream')
            ->willReturn($stream);

        $aggregate
            ->expects($this->once())
            ->method('commit');

        /**
         * @var EventStoreInterface            $eventStore
         * @var EventPublisherInterface        $eventPublisher
         * @var EventSourcedAggregateInterface $aggregate
         */
        $repository = new EventStoreRepository($eventStore, $eventPublisher, EventSourcedAggregateStub::class);
        $repository->save($aggregate);
    }

    /**
     * Aggregate not event sourced.
     *
     * Test that an exception will be thrown when aggregate is not event sourced.
     *
     * @covers                   \ExtendsFramework\Sourcing\Command\Repository\EventStoreRepository::save()
     * @covers                   \ExtendsFramework\Sourcing\Command\Repository\Exception\AggregateNotEventSourced::__construct()
     * @expectedException        \ExtendsFramework\Sourcing\Command\Repository\Exception\AggregateNotEventSourced
     * @expectedExceptionMessage Can not save stream to event store because aggregate is not event sourced.
     */
    public function testAggregateNotEventSourced(): void
    {
        $eventStore = $this->createMock(EventStoreInterface::class);

        $eventPublisher = $this->createMock(EventPublisherInterface::class);

        $aggregate = $this->createMock(AggregateInterface::class);

        /**
         * @var EventStoreInterface     $eventStore
         * @var EventPublisherInterface $eventPublisher
         * @var AggregateInterface      $aggregate
         */
        $repository = new EventStoreRepository($eventStore, $eventPublisher, EventSourcedAggregateStub::class);
        $repository->save($aggregate);
    }
}

class EventSourcedAggregateStub extends EventSourcedAggregate
{
}
