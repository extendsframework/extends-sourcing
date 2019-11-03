<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Handler;

use ExtendsFramework\Command\CommandMessageInterface;
use ExtendsFramework\Command\Handler\CommandHandlerInterface;
use ExtendsFramework\Command\Repository\RepositoryException;
use ExtendsFramework\Command\Repository\RepositoryInterface;

class ProxyCommandHandler implements CommandHandlerInterface
{
    /**
     * Aggregate repository.
     *
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * AggregateCommandHandler constructor.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     * @throws RepositoryException
     */
    public function handle(CommandMessageInterface $commandMessage): void
    {
        $aggregate = $this->repository->load($commandMessage->getAggregateId());
        $aggregate->handle($commandMessage);

        $this->repository->save($aggregate);
    }
}
