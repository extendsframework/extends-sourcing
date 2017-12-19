<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Repository\Exception;

use ExtendsFramework\Command\Repository\RepositoryException;
use LogicException;

class AggregateNotEventSourced extends LogicException implements RepositoryException
{
    /**
     * AggregateNotEventSourced constructor.
     */
    public function __construct()
    {
        parent::__construct('Can not save stream to event store because aggregate is not event sourced.');
    }
}
