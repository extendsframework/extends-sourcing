<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Store;

use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;

interface EventStoreInterface
{
    /**
     * Load stream for identifier from event store.
     *
     * @param string $identifier
     * @return StreamInterface
     * @throws EventStoreException
     */
    public function loadStream(string $identifier): StreamInterface;

    /**
     * Save stream to event store.
     *
     * @param StreamInterface $stream
     * @throws EventStoreException
     */
    public function saveStream(StreamInterface $stream): void;
}
