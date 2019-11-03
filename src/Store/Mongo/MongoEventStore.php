<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Store\Mongo;

use ExtendsFramework\Message\Payload\Type\PayloadType;
use ExtendsFramework\Serializer\SerializedObject;
use ExtendsFramework\Serializer\SerializerException;
use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\Sourcing\Event\Message\DomainEventMessage;
use ExtendsFramework\Sourcing\Event\Stream\Stream;
use ExtendsFramework\Sourcing\Event\Stream\StreamInterface;
use ExtendsFramework\Sourcing\Store\EventStoreInterface;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoEventStore implements EventStoreInterface
{
    /**
     * Mongo manager.
     *
     * @var Manager
     */
    private $manager;

    /**
     * Namespace.
     *
     * @var string
     */
    private $namespace;

    /**
     * Object serializer.
     *
     * @var SerializerInterface
     */
    private $serializer;

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
     * @throws Exception
     * @throws SerializerException
     */
    public function loadStream(string $identifier): StreamInterface
    {
        $cursor = $this->manager->executeQuery(
            $this->namespace,
            new Query([
                'aggregate_id' => $identifier,
            ], [
                'sort' => [
                    'sequence' => 1,
                ],
            ])
        );

        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        $messages = [];
        foreach ($cursor as $document) {
            $payload = $this->serializer->unserialize(new SerializedObject(
                $document['payload']['name'],
                $document['payload']['data']
            ));

            /** @noinspection PhpParamsInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $messages[] = new DomainEventMessage(
                $payload,
                new PayloadType($payload),
                $document['occurred_on']->toDateTime(),
                $document['aggregate_id'],
                $document['sequence'],
                $document['meta_data']
            );
        }

        return new Stream($identifier, count($messages), $messages);
    }

    /**
     * @inheritDoc
     * @throws SerializerException
     */
    public function saveStream(StreamInterface $stream): void
    {
        $bulkWrite = new BulkWrite(['ordered' => true]);

        foreach ($stream as $domainEventMessage) {
            $payload = $this->serializer->serialize($domainEventMessage->getPayload());
            $bulkWrite->insert([
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
            ]);
        }

        $this->manager->executeBulkWrite(
            $this->namespace,
            $bulkWrite,
            new WriteConcern(1, 10000, true)
        );
    }
}
