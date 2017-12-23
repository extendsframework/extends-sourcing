<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Handler;

use ExtendsFramework\Command\CommandMessageInterface;
use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Command\Repository\RepositoryInterface;
use ExtendsFramework\Message\Payload\Type\PayloadTypeInterface;
use PHPUnit\Framework\TestCase;

class ProxyCommandHandlerTest extends TestCase
{
    /**
     * Handle.
     *
     * Test that correct aggregate will be loaded from repository and command message will be passed on to the
     * aggregate.
     *
     * @covers \ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler::__construct()
     * @covers \ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler::handle()
     * @covers \ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler::getRepository()
     * @covers \ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler::loadAggregate()
     * @covers \ExtendsFramework\Sourcing\Command\Handler\ProxyCommandHandler::saveAggregate()
     */
    public function testHandle(): void
    {
        $payloadType = $this->createMock(PayloadTypeInterface::class);
        $payloadType
            ->method('getName')
            ->willReturn('FooBar');

        $commandMessage = $this->createMock(CommandMessageInterface::class);
        $commandMessage
            ->method('getPayloadType')
            ->willReturn($payloadType);

        $commandMessage
            ->method('getAggregateId')
            ->willReturn('09c3a960-de5e-4cb7-a0be-a6bfc73ee65c');

        $aggregate = $this->createMock(AggregateInterface::class);
        $aggregate
            ->expects($this->once())
            ->method('handle')
            ->with($commandMessage);

        $repository = $this->createMock(RepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('load')
            ->with('09c3a960-de5e-4cb7-a0be-a6bfc73ee65c')
            ->willReturn($aggregate);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($aggregate);

        /**
         * @var RepositoryInterface     $repository
         * @var CommandMessageInterface $commandMessage
         */
        $handler = new ProxyCommandHandler($repository);
        $handler->handle($commandMessage);
    }
}
