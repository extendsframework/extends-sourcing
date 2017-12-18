<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Stream;

use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    /**
     * Iterate.
     *
     * Test that stream can be iterated.
     *
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::__construct()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::current()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::next()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::key()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::valid()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::rewind()
     */
    public function testIterate(): void
    {
        $message = $this->createMock(DomainEventMessageInterface::class);

        $stream = new Stream([
            $message,
            $message,
            $message,
        ]);

        foreach ($stream as $domainEventMessage) {
            $this->assertSame($message, $domainEventMessage);
        }
    }

    /**
     * Count.
     *
     * Test that count will return correct value.
     *
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::__construct()
     * @covers \ExtendsFramework\Sourcing\Event\Stream\Stream::count()
     */
    public function testCount(): void
    {
        $message = $this->createMock(DomainEventMessageInterface::class);

        $stream = new Stream([
            $message,
            $message,
            $message,
        ]);

        $this->assertSame(3, $stream->count());
    }
}
