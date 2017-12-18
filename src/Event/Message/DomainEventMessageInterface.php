<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Message;

use ExtendsFramework\Event\EventMessageInterface;

interface DomainEventMessageInterface extends EventMessageInterface
{
    /**
     * Get aggregate id.
     *
     * @return string
     */
    public function getAggregateId(): string;

    /**
     * Get sequence number.
     *
     * @return int
     */
    public function getSequence(): int;
}
