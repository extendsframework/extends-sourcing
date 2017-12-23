<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Repository;

use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Command\Repository\RepositoryInterface;
use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregateInterface;
use ExtendsFramework\Sourcing\Command\Repository\Exception\AggregateNotEventSourced;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use ExtendsFramework\Sourcing\Store\EventStoreException;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;

class EventStoreRepository implements RepositoryInterface
{
    /**
     * Event store.
     *
     * @var EventStoreInterface
     */
    protected $eventStore;

    /**
     * Event publisher.
     *
     * @var EventPublisherInterface
     */
    protected $eventPublisher;

    /**
     * Aggregate to initialize.
     *
     * @var EventSourcedAggregateInterface
     */
    protected $aggregate;

    /**
     * EventSourcedRepository constructor.
     *
     * @param EventStoreInterface            $eventStore
     * @param EventPublisherInterface        $eventPublisher
     * @param EventSourcedAggregateInterface $aggregate
     */
    public function __construct(EventStoreInterface $eventStore, EventPublisherInterface $eventPublisher, EventSourcedAggregateInterface $aggregate)
    {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;
        $this->aggregate = $aggregate;
    }

    /**
     * @inheritDoc
     */
    public function load(string $identifier): AggregateInterface
    {
        $aggregate = $this->getAggregate();
        $aggregate->initialize(
            $this->loadStream($identifier)
        );

        return $aggregate;
    }

    /**
     * @inheritDoc
     */
    public function save(AggregateInterface $aggregate): void
    {
        if (!$aggregate instanceof EventSourcedAggregateInterface) {
            throw new AggregateNotEventSourced();
        }

        $stream = $aggregate->getStream();
        $aggregate->commit();

        $this
            ->saveStream($stream)
            ->publishStream($stream);
    }

    /**
     * Get aggregate to initialize.
     *
     * Aggregate must be cloned because every aggregate will have its own state after initialization and therefor can
     * not be reused.
     *
     * @return EventSourcedAggregateInterface
     */
    protected function getAggregate(): EventSourcedAggregateInterface
    {
        return clone $this->aggregate;
    }

    /**
     * Load stream for identifier.
     *
     * @param string $identifier
     * @return StreamInterface
     * @throws EventStoreException
     */
    protected function loadStream(string $identifier): StreamInterface
    {
        return $this
            ->getEventStore()
            ->loadStream($identifier);
    }

    /**
     * Save stream to event store.
     *
     * @param StreamInterface $stream
     * @return EventStoreRepository
     * @throws EventStoreException
     */
    protected function saveStream(StreamInterface $stream): EventStoreRepository
    {
        $this
            ->getEventStore()
            ->saveStream($stream);

        return $this;
    }

    /**
     * Publish stream to event listeners.
     *
     * @param StreamInterface $stream
     * @return EventStoreRepository
     */
    protected function publishStream(StreamInterface $stream): EventStoreRepository
    {
        foreach ($stream as $domainEventMessage) {
            $this
                ->getEventPublisher()
                ->publish($domainEventMessage);
        }

        return $this;
    }

    /**
     * Get event store.
     *
     * @return EventStoreInterface
     */
    protected function getEventStore(): EventStoreInterface
    {
        return $this->eventStore;
    }

    /**
     * Get event publisher.
     *
     * @return EventPublisherInterface
     */
    protected function getEventPublisher(): EventPublisherInterface
    {
        return $this->eventPublisher;
    }
}
