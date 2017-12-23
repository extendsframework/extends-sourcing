<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use DateTime;
use ExtendsFramework\Command\Handler\AbstractCommandHandler;
use ExtendsFramework\Message\Payload\Exception\MethodNotFound;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadType;
use ExtendsFramework\Sourcing\Command\Model\Exception\AggregateAlreadyInitialized;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessage;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\Stream;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;

abstract class EventSourcedAggregate extends AbstractCommandHandler implements EventSourcedAggregateInterface
{
    /**
     * Aggregate id.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Aggregate version.
     *
     * @var int
     */
    protected $version;

    /**
     * If aggregate is already initialized with event stream.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Recorded domain event messages.
     *
     * @var DomainEventMessageInterface[]
     */
    protected $domainEventMessages = [];

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): int
    {
        return $this->version;
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
    public function initialize(StreamInterface $stream): void
    {
        if ($this->isInitialized() === true) {
            throw new AggregateAlreadyInitialized($stream);
        }

        $this->identifier = $stream->getAggregateId();
        $this->version = $stream->getVersion();

        foreach ($stream as $domainEventMessage) {
            $this->apply($domainEventMessage);
        }

        $this->initialized = true;
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

    /**
     * If aggregate is already initialized.
     *
     * @return bool
     */
    protected function isInitialized(): bool
    {
        return $this->initialized;
    }
}
