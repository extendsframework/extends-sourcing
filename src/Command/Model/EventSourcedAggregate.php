<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use DateTime;
use ExtendsFramework\Command\Model\AbstractAggregate;
use ExtendsFramework\Message\Payload\Exception\MethodNotFound;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadType;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessage;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\Stream;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;

abstract class EventSourcedAggregate extends AbstractAggregate implements EventSourcedAggregateInterface
{
    /**
     * Recorded events.
     *
     * @var DomainEventMessageInterface[]
     */
    protected $domainEventMessages = [];

    /**
     * EventSourcedAggregate constructor.
     *
     * @param StreamInterface $stream
     * @throws MethodNotFound
     */
    public function __construct(StreamInterface $stream)
    {
        parent::__construct($stream->getAggregateId(), $stream->getVersion());

        foreach ($stream as $domainEventMessage) {
            $this->apply($domainEventMessage);
        }
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->domainEventMessages = [];
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        return new Stream(
            $this->getIdentifier(),
            $this->getVersion(),
            $this->getRecordedEvents()
        );
    }

    /**
     * Apply domain event message to aggregate.
     *
     * @param DomainEventMessageInterface $domainEventMessage
     * @return EventSourcedAggregate
     * @throws MethodNotFound
     */
    protected function apply(DomainEventMessageInterface $domainEventMessage): EventSourcedAggregate
    {
        $this->getMethod($domainEventMessage, 'apply')($domainEventMessage->getPayload());

        return $this;
    }

    /**
     * Record payload and meta data into a new domain event message.
     *
     * Meta data from the command message will be recursively replaced with given meta data and added to the domain
     * event message.
     *
     * @param PayloadInterface $payload
     * @param array|null       $metaData
     * @throws MethodNotFound
     */
    protected function record(PayloadInterface $payload, array $metaData = null): void
    {
        $domainEventMessage = new DomainEventMessage(
            $payload,
            new PayloadType($payload),
            new DateTime(),
            $this->getIdentifier(),
            $this->getNextVersion(),
            array_replace_recursive(
                $this
                    ->getCommandMessage()
                    ->getMetaData(),
                $metaData
            )
        );

        $this
            ->apply($domainEventMessage)
            ->addDomainEventMessage($domainEventMessage);
    }

    /**
     * Add domain event message.
     *
     * @param DomainEventMessageInterface $domainEventMessage
     * @return EventSourcedAggregate
     */
    protected function addDomainEventMessage(DomainEventMessageInterface $domainEventMessage): EventSourcedAggregate
    {
        $this->domainEventMessages[] = $domainEventMessage;

        return $this;
    }

    /**
     * Get recorded events.
     *
     * @return DomainEventMessageInterface[]
     */
    protected function getRecordedEvents(): array
    {
        return $this->domainEventMessages;
    }

    /**
     * Get next aggregate version.
     *
     * @return int
     */
    protected function getNextVersion(): int
    {
        return ++$this->version;
    }
}
