<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Store\Mongo;

use ExtendsFramework\Message\Payload\PayloadInterface;
use ExtendsFramework\Message\Payload\Type\PayloadType;
use ExtendsFramework\Serializer\SerializedObject;
use ExtendsFramework\Serializer\SerializerException;
use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessage;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessageInterface;
use ExtendsFramework\Sourcing\Event\Stream\Stream;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

class MongoEventStore implements EventStoreInterface
{
    /**
     * Mongo manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Object serializer.
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * MongoEventStore constructor.
     *
     * @param Manager             $manager
     * @param string              $namespace
     * @param SerializerInterface $serializer
     */
    public function __construct(Manager $manager, string $namespace, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->namespace = $namespace;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function load(string $aggregateId): StreamInterface
    {
        $cursor = $this
            ->getManager()
            ->executeQuery(
                $this->getNamespace(),
                $this->getQuery($aggregateId)
            );
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        $messages = [];
        foreach ($cursor as $document) {
            $messages[] = $this->getDomainEventMessage($document);
        }

        return new Stream($aggregateId, count($messages), $messages);
    }

    /**
     * @inheritDoc
     */
    public function save(StreamInterface $stream): void
    {
        $bulkWrite = new BulkWrite();

        foreach ($stream as $message) {
            $bulkWrite->insert(
                $this->getMongoDocument($message)
            );
        }

        $this
            ->getManager()
            ->executeBulkWrite(
                $this->getNamespace(),
                $bulkWrite
            );
    }

    /**
     * Get Mongo document from domain event message.
     *
     * @param DomainEventMessageInterface $domainEventMessage
     * @return array
     * @throws SerializerException
     */
    protected function getMongoDocument(DomainEventMessageInterface $domainEventMessage): array
    {
        $payload = $this
            ->getSerializer()
            ->serialize($domainEventMessage->getPayload());

        return [
            'aggregate_id' => $domainEventMessage->getAggregateId(),
            'sequence' => $domainEventMessage->getSequence(),
            'occurred_on' => new UTCDateTime(
                $domainEventMessage
                    ->getOccurredOn()
                    ->format('Uv')
            ),
            'payload' => [
                'name' => $payload->getClassName(),
                'data' => (object)$payload->getData(),
            ],
            'meta_data' => (object)$domainEventMessage->getMetaData(),
        ];
    }

    /**
     * Get domain event message from Mongo document.
     *
     * @param array $document
     * @return DomainEventMessageInterface
     * @throws SerializerException
     */
    protected function getDomainEventMessage(array $document): DomainEventMessageInterface
    {
        $payload = $this
            ->getSerializer()
            ->unserialize(new SerializedObject(
                $document['payload']['name'],
                $document['payload']['data']
            ));

        /** @var PayloadInterface $payload */
        return new DomainEventMessage(
            $payload,
            new PayloadType($payload),
            $document['occurred_on']->toDateTime(),
            $document['aggregate_id'],
            $document['sequence'],
            $document['meta_data']
        );
    }

    /**
     * Get Mongo cursor for aggregate id.
     *
     * @param string $aggregateId
     * @return Query
     */
    protected function getQuery(string $aggregateId): Query
    {
        return new Query([
            'aggregate_id' => $aggregateId,
        ], [
            'sort' => [
                'sequence' => 1,
            ],
        ]);
    }

    /**
     * Get mongo manager.
     *
     * @return Manager
     */
    protected function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * Get object serializer.
     *
     * @return SerializerInterface
     */
    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Get namespace.
     *
     * @return string
     */
    protected function getNamespace(): string
    {
        return $this->namespace;
    }
}
