<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use DateTime;
use Exception;
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
    private $identifier;

    /**
     * Aggregate version.
     *
     * @var int
     */
    private $version;

    /**
     * If aggregate is already initialized with event stream.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Recorded domain event messages.
     *
     * @var DomainEventMessageInterface[]
     */
    private $domainEventMessages = [];

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
        if ($this->initialized) {
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
        return new Stream($this->identifier, $this->version, $this->domainEventMessages);
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
     * @throws Exception
     */
    protected function record(PayloadInterface $payload, array $metaData = null): void
    {
        $this->version++;
        
        $domainEventMessage = new DomainEventMessage(
            $payload,
            new PayloadType($payload),
            new DateTime(),
            $this->getIdentifier(),
            $this->version,
            array_replace_recursive(
                $this
                    ->getCommandMessage()
                    ->getMetaData(),
                $metaData
            )
        );

        $this->apply($domainEventMessage);
        $this->domainEventMessages[] = $domainEventMessage;
    }

    /**
     * Apply domain event message to aggregate.
     *
     * @param DomainEventMessageInterface $domainEventMessage
     * @return EventSourcedAggregate
     * @throws MethodNotFound
     */
    private function apply(DomainEventMessageInterface $domainEventMessage): EventSourcedAggregate
    {
        $this->getMethod($domainEventMessage, 'on')($domainEventMessage->getPayload());

        return $this;
    }
}
