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
     * @inheritDoc
     */
    public function current()
    {
        return current($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        next($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        reset($this->messages);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->messages);
    }

    /**
     * Add domain event message to stream.
     *
     * @param DomainEventMessageInterface $domainEventMessage
     * @return Stream
     */
    public function addMessage(DomainEventMessageInterface $domainEventMessage): Stream
    {
        $this->messages[] = $domainEventMessage;

        return $this;
    }
}
