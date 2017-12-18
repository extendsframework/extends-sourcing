<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Stream;

use Countable;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use Iterator;

interface StreamInterface extends Iterator, Countable
{
    /**
     * Get current domain event message.
     *
     * @return DomainEventMessageInterface
     */
    public function current(): DomainEventMessageInterface;

    /**
     * Get aggregate identifier.
     *
     * @return string
     */
    public function getAggregateId(): string;

    /**
     * Get aggregate version.
     *
     * @return int
     */
    public function getVersion(): int;
}
