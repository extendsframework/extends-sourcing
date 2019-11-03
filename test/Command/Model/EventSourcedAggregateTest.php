<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use ExtendsFramework\Command\CommandMessageInterface;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadTypeInterface;
use ExtendsFramework\Sourcing\Command\Model\Exception\AggregateAlreadyInitialized;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use PHPUnit\Framework\TestCase;

class EventSourcedAggregateTest extends TestCase
{
    /**
     * Construct.
     *
     * Test that aggregate will be constructed and domain event message will be applied.
     *
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::initialize()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::getIdentifier()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::getVersion()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::apply()
     */
    public function testInitialize(): void
    {
        $payload = $this->createMock(PayloadInterface::class);

        $payloadType = $this->createMock(PayloadTypeInterface::class);
        $payloadType
            ->method('getName')
            ->willReturn('FooBar');

        $domainEventMessage = $this->createMock(DomainEventMessageInterface::class);
        $domainEventMessage
            ->method('getPayload')
            ->willReturn($payload);

        $domainEventMessage
            ->method('getPayloadType')
            ->willReturn($payloadType);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('getAggregateId')
            ->willReturn('bar');

        $stream
            ->method('getVersion')
            ->willReturn(12);

        $stream
            ->expects($this->at(2))
            ->method('rewind');

        $stream
            ->expects($this->at(3))
            ->method('valid')
            ->willReturn(true);

        $stream
            ->expects($this->at(4))
            ->method('current')
            ->willReturn($domainEventMessage);

        /**
         * @var StreamInterface $stream
         */
        $aggregate = new EventSourcedAggregateStub();
        $aggregate->initialize($stream);

        $this->assertSame('bar', $aggregate->getIdentifier());
        $this->assertSame(12, $aggregate->getVersion());
        $this->assertSame($payload, $aggregate->getPayload());
    }

    /**
     * Record.
     *
     * Test that handled command message will result in a domain event message.
     *
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::initialize()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::record()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::getStream()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::apply()
     * @covers \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::commit()
     */
    public function testRecord(): void
    {
        $payload = $this
            ->getMockBuilder(PayloadInterface::class)
            ->setMockClassName('FooBar')
            ->getMock();

        $payloadType = $this->createMock(PayloadTypeInterface::class);
        $payloadType
            ->method('getName')
            ->willReturn('FooBar');

        $commandMessage = $this->createMock(CommandMessageInterface::class);
        $commandMessage
            ->method('getPayload')
            ->willReturn($payload);

        $commandMessage
            ->method('getPayloadType')
            ->willReturn($payloadType);

        $commandMessage
            ->method('getMetaData')
            ->willReturn(['foo' => 'bar']);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('getAggregateId')
            ->willReturn('bar');

        $stream
            ->method('getVersion')
            ->willReturn(12);

        /**
         * @var StreamInterface         $stream
         * @var CommandMessageInterface $commandMessage
         */
        $aggregate = new EventSourcedAggregateStub();
        $aggregate->initialize($stream);
        $aggregate->handle($commandMessage);

        $this->assertSame(13, $aggregate->getVersion());

        $stream = $aggregate->getStream();
        $current = $stream->current();

        $this->assertCount(1, $stream);
        $this->assertSame($payload, $current->getPayload());
        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $current->getMetaData());

        $aggregate->commit();

        $this->assertCount(0, $aggregate->getStream());
    }

    /**
     * Aggregate already initialized.
     *
     * Test that an exception will be thrown when aggregate is already initialized.
     *
     * @covers                   \ExtendsFramework\Sourcing\Command\Model\EventSourcedAggregate::initialize()
     * @covers                   \ExtendsFramework\Sourcing\Command\Model\Exception\AggregateAlreadyInitialized::__construct()
     * @expectedException        AggregateAlreadyInitialized
     * @expectedExceptionMessage Can not load stream for id "bar", aggregate already initialized.
     */
    public function testAggregateAlreadyInitialized(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('getAggregateId')
            ->willReturn('bar');

        $stream
            ->method('getVersion')
            ->willReturn(12);

        /**
         * @var StreamInterface $stream
         */
        $aggregate = new EventSourcedAggregateStub();
        $aggregate->initialize($stream);
        $aggregate->initialize($stream);
    }
}
