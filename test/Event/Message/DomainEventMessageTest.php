<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Event\Message;

use DateTime;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadTypeInterface;
use PHPUnit\Framework\TestCase;

class DomainEventMessageTest extends TestCase
{
    /**
     * Get methods.
     *
     * Test that get methods will return correct value.
     *
     * @covers \ExtendsFramework\Sourcing\Event\Message\DomainEventMessage::__construct()
     * @covers \ExtendsFramework\Sourcing\Event\Message\DomainEventMessage::getAggregateId()
     * @covers \ExtendsFramework\Sourcing\Event\Message\DomainEventMessage::getSequence()
     */
    public function testGetMethods(): void
    {
        $payload = $this->createMock(PayloadInterface::class);

        $payloadType = $this->createMock(PayloadTypeInterface::class);

        $dateTime = $this->createMock(DateTime::class);

        /**
         * @var PayloadInterface     $payload
         * @var PayloadTypeInterface $payloadType
         * @var DateTime             $dateTime
         */
        $message = new DomainEventMessage($payload, $payloadType, $dateTime, 'foo', 3, ['foo' => 'bar']);

        $this->assertSame($payload, $message->getPayload());
        $this->assertSame($payloadType, $message->getPayloadType());
        $this->assertSame($dateTime, $message->getOccurredOn());
        $this->assertSame('foo', $message->getAggregateId());
        $this->assertSame(3, $message->getSequence());
        $this->assertSame(['foo' => 'bar'], $message->getMetaData());
    }
}
