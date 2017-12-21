<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Store\Mongo;

use DateTime;
use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Serializer\SerializedObjectInterface;
use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;

class MongoEventStoreTest extends TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Clear Mongo collection.
     */
    public function clearCollection(): void
    {
        $bulkWrite = new BulkWrite();
        $bulkWrite->delete([]);

        $this->manager->executeBulkWrite($this->namespace, $bulkWrite);
    }

    /**
     * Set up Mongo driver manager and clear collection.
     */
    public function setUp(): void
    {
        $this->manager = new Manager($GLOBALS['MONGO_URI']);
        $this->namespace = $GLOBALS['MONGO_NAMESPACE'];

        $this->clearCollection();
    }

    /**
     * Clear Mongo collection.
     */
    public function tearDown(): void
    {
        $this->clearCollection();
    }

    /**
     * Save and load.
     *
     * Test that saved stream will be loaded and the domain event message are in the correct sequence order.
     *
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::__construct()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::save()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getMongoDocument()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::load()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getDomainEventMessage()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getManager()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getSerializer()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getNamespace()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getQuery()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getBulkWrite()
     * @covers \ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore::getWriteConcern()
     */
    public function testSaveAndLoad(): void
    {
        $aggregateId = '7e4d78f6-6d66-4f2c-ac28-d21131d551c8';

        $domainEventMessage = $this->createMock(DomainEventMessageInterface::class);
        $domainEventMessage
            ->method('getAggregateId')
            ->willReturn($aggregateId);

        $domainEventMessage
            ->method('getSequence')
            ->willReturnOnConsecutiveCalls(
                5,
                1,
                3,
                4,
                2
            );

        $domainEventMessage
            ->method('getOccurredOn')
            ->willReturn(new DateTime());

        $payload = $this->createMock(PayloadInterface::class);

        $domainEventMessage
            ->method('getPayload')
            ->willReturn($payload);

        $domainEventMessage
            ->method('getMetaData')
            ->willReturn([
                'foo' => 'bar',
            ]);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('valid')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                true,
                true,
                true,
                false
            );

        $stream
            ->method('current')
            ->willReturn($domainEventMessage);

        $serialized = $this->createMock(SerializedObjectInterface::class);
        $serialized
            ->method('getClassName')
            ->willReturn('FooBar');

        $serialized
            ->method('getData')
            ->willReturn([
                'some' => 'data',
            ]);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->method('serialize')
            ->with($payload)
            ->willReturn($serialized);

        $serializer
            ->method('unserialize')
            ->with($this->isInstanceOf(SerializedObjectInterface::class))
            ->willReturn($this->createMock(PayloadInterface::class));

        /**
         * @var SerializerInterface $serializer
         * @var StreamInterface     $stream
         */
        $eventStore = new MongoEventStore($this->manager, $this->namespace, $serializer);
        $eventStore->save($stream);

        $loaded = $eventStore->load($aggregateId);
        $this->assertCount(5, $loaded);

        foreach ($loaded as $index => $domainEventMessage) {
            $this->assertSame($index + 1, $domainEventMessage->getSequence());
        }
    }
}
