<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Stream;

use Countable;
use Iterator;

interface StreamInterface extends Iterator, Countable
{
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
