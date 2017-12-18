<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Stream;

use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;

class Stream implements StreamInterface
{
    /**
     * Domain event messages.
     *
     * @var DomainEventMessageInterface[]
     */
    protected $messages = [];

    /**
     * Stream constructor.
     *
     * @param DomainEventMessageInterface[] $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
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
