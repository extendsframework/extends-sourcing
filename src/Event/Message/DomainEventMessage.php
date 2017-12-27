<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Message;

use DateTime;
use ExtendsFramework\Event\EventMessage;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadTypeInterface;

class DomainEventMessage extends EventMessage implements DomainEventMessageInterface
{
    /**
     * Aggregate id.
     *
     * @var string
     */
    protected $aggregateId;

    /**
     * Sequence number.
     *
     * @var int
     */
    protected $sequence;

    /**
     * @inheritDoc
     */
    public function __construct(
        PayloadInterface $payload,
        PayloadTypeInterface $payloadType,
        DateTime $occurredOn,
        string $aggregateId,
        int $sequence,
        array $metaData
    ) {
        parent::__construct($payload, $payloadType, $occurredOn, $metaData);

        $this->aggregateId = $aggregateId;
        $this->sequence = $sequence;
    }

    /**
     * @inheritDoc
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @inheritDoc
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }
}
