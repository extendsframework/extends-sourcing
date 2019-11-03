<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Repository;

use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Command\Repository\RepositoryInterface;
use ExtendsFramework\Event\Publisher\EventPublisherInterface;
use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregateInterface;
use ExtendsFramework\Sourcing\Command\Repository\Exception\AggregateNotEventSourced;
use ExtendsFramework\Sourcing\Store\EventStoreException;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;

class EventStoreRepository implements RepositoryInterface
{
    /**
     * Event store.
     *
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * Event publisher.
     *
     * @var EventPublisherInterface
     */
    private $eventPublisher;

    /**
     * Aggregate to initialize.
     *
     * @var EventSourcedAggregateInterface
     */
    private $aggregate;

    /**
     * EventSourcedRepository constructor.
     *
     * @param EventStoreInterface            $eventStore
     * @param EventPublisherInterface        $eventPublisher
     * @param EventSourcedAggregateInterface $aggregate
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventPublisherInterface $eventPublisher,
        EventSourcedAggregateInterface $aggregate
    ) {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;
        $this->aggregate = $aggregate;
    }

    /**
     * @inheritDoc
     * @throws EventStoreException
     */
    public function load(string $identifier): AggregateInterface
    {
        $aggregate = clone $this->aggregate;
        $aggregate->initialize($this->eventStore->loadStream($identifier));

        return $aggregate;
    }

    /**
     * @inheritDoc
     * @throws EventStoreException
     */
    public function save(AggregateInterface $aggregate): void
    {
        if (!$aggregate instanceof EventSourcedAggregateInterface) {
            throw new AggregateNotEventSourced();
        }

        $stream = $aggregate->getStream();
        $aggregate->commit();

        $this->eventStore->saveStream($stream);
        foreach ($stream as $domainEventMessage) {
            $this->eventPublisher->publish($domainEventMessage);
        }
    }
}
