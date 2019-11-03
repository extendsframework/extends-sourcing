<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Model;

use Exception;
use ExtendsFramework\Message\Payload\Exception\MethodNotFound;
use ExtendsFramework\Message\Payload\PayloadInterface;

class EventSourcedAggregateStub extends EventSourcedAggregate
{
    /**
     * @var PayloadInterface
     */
    protected $payload;

    /**
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }

    /**
     * @param PayloadInterface $payload
     * @throws MethodNotFound
     * @throws Exception
     */
    protected function handleFooBar(PayloadInterface $payload): void
    {
        $this->record($payload, ['bar' => 'baz']);
    }

    /**
     * @param PayloadInterface $payload
     */
    protected function onFooBar(PayloadInterface $payload): void
    {
        $this->payload = $payload;
    }
}
