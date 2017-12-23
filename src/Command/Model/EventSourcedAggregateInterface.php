<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;

interface EventSourcedAggregateInterface extends AggregateInterface
{
    /**
     * Commit aggregate.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Load stream into aggregate.
     *
     * @param StreamInterface $stream
     */
    public function initialize(StreamInterface $stream): void;

    /**
     * Get stream.
     *
     * @return StreamInterface
     */
    public function getStream(): StreamInterface;
}
