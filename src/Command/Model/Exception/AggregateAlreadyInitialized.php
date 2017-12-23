<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model\Exception;

use ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregateException;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use LogicException;

class AggregateAlreadyInitialized extends LogicException implements EventSourcedAggregateException
{
    /**
     * AggregateAlreadyInitialized constructor.
     *
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        parent::__construct(sprintf(
            'Can not load stream for id "%s", aggregate already initialized.',
            $stream->getAggregateId()
        ));
    }
}
