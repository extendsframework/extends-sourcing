<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Command\Handler;

use ExtendsFramework\Command\CommandMessageInterface;
use ExtendsFramework\Command\Handler\CommandHandlerInterface;
use ExtendsFramework\Command\Model\AggregateInterface;
use ExtendsFramework\Command\Repository\RepositoryException;
use ExtendsFramework\Command\Repository\RepositoryInterface;

class ProxyCommandHandler implements CommandHandlerInterface
{
    /**
     * Aggregate repository.
     *
     * @var RepositoryInterface
     */
    protected $repository;

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
     */
    public function handle(CommandMessageInterface $commandMessage): void
    {
        $aggregate = $this->loadAggregate($commandMessage);
        $aggregate->handle($commandMessage);

        $this->saveAggregate($aggregate);
    }

    /**
     * Get aggregate instance for command message.
     *
     * @param CommandMessageInterface $commandMessage
     * @return AggregateInterface
     * @throws RepositoryException
     */
    protected function loadAggregate(CommandMessageInterface $commandMessage): AggregateInterface
    {
        return $this
            ->getRepository()
            ->load($commandMessage->getAggregateId());
    }

    /**
     * Save aggregate to repository.
     *
     * @param AggregateInterface $aggregate
     */
    protected function saveAggregate(AggregateInterface $aggregate): void
    {
        $this
            ->getRepository()
            ->save($aggregate);
    }

    /**
     * Get repository.
     *
     * @return RepositoryInterface
     */
    protected function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
