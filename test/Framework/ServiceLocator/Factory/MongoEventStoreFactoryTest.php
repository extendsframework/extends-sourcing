<?php
declare(strict_types=1);

namespace ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory;

use ExtendsFramework\Serializer\SerializerInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Sourcing\Store\Mongo\MongoEventStore;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;

class MongoEventStoreFactoryTest extends TestCase
{
    /**
     * Create service.
     *
     * Test that correct service will be created.
     *
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\MongoEventStoreFactory::createService()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\MongoEventStoreFactory::getManager()
     * @covers \ExtendsFramework\Sourcing\Framework\ServiceLocator\Factory\MongoEventStoreFactory::getSerializer()
     */
    public function testCreateService(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->method('getConfig')
            ->willReturn([
                MongoEventStore::class => [
                    'namespace' => $GLOBALS['MONGO_NAMESPACE'],
                ],
            ]);

        $serviceLocator
            ->method('getService')
            ->withConsecutive(
                [Manager::class],
                [SerializerInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                new Manager($GLOBALS['MONGO_URI']),
                $this->createMock(SerializerInterface::class)
            );

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $factory = new MongoEventStoreFactory();
        $eventStore = $factory->createService(MongoEventStore::class, $serviceLocator);

        $this->assertInstanceOf(MongoEventStore::class, $eventStore);
    }
}
