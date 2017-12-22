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
use ReflectionClass;

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
     * Aggregate class to instantiate.
     *
     * @var string
     */
    protected $aggregateClass;

    /**
     * EventSourcedRepository constructor.
     *
     * @param EventStoreInterface     $eventStore
     * @param EventPublisherInterface $eventPublisher
     * @param string                  $aggregateClass
     */
    public function __construct(EventStoreInterface $eventStore, EventPublisherInterface $eventPublisher, string $aggregateClass)
    {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;
        $this->aggregateClass = $aggregateClass;
    }

    /**
     * @inheritDoc
     */
    public function load(string $identifier): AggregateInterface
    {
        return $this->getAggregate(
            $this->loadStream($identifier)
        );
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
     * Get aggregate with stream.
     *
     * @param StreamInterface $stream
     * @return EventSourcedAggregateInterface|object
     */
    protected function getAggregate(StreamInterface $stream): EventSourcedAggregateInterface
    {
        $class = new ReflectionClass($this->aggregateClass);

        return $class->newInstance($stream);
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
