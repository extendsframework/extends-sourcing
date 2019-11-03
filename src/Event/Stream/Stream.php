<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Stream;

use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;

class Stream implements StreamInterface
{
    /**
     * Aggregate identifier.
     *
     * @var string
     */
    private $aggregateId;

    /**
     * Aggregate version.
     *
     * @var int
     */
    private $version;

    /**
     * Domain event messages.
     *
     * @var DomainEventMessageInterface[]
     */
    private $messages;

    /**
     * Stream constructor.
     *
     * @param string                        $aggregateId
     * @param int                           $version
     * @param DomainEventMessageInterface[] $messages
     */
    public function __construct(string $aggregateId, int $version, array $messages)
    {
        $this->aggregateId = $aggregateId;
        $this->version = $version;
        $this->messages = $messages;
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
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function current(): DomainEventMessageInterface
    {
        return current($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function key(): ?int
    {
        return key($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->messages);
    }
}
