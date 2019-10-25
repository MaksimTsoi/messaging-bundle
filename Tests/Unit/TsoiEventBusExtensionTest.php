<?php


namespace Tsoi\EventBus\Tests\Unit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Tsoi\EventBusBundle\DependencyInjection\TsoiEventBusExtension;

class TsoiEventBusExtensionTest extends AbstractExtensionTestCase
{
    protected function getMinimalConfiguration()
    {
        return [
            'current_microservice' => 'micro1',
//            'default_connection' => [
//                'host' => 'rabbitmq',
//                'port' => 5672,
//                'user_name' => 'guest',
//                'password' => 'guest',
//            ],
//            'microservices' => [
//                'micro1' => []
//            ]
        ];
    }

    public function testEventBusLoaded()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.event_bus', 0, 'Tsoi\EventBusBundle\EventBus\Amqp\Publisher');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.event_bus', 1, 'Tsoi\EventBusBundle\EventBus\Amqp\Consumer');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.event_bus', 2, 'service_container');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.event_bus', 3, 'Tsoi\EventBusBundle\EventBus\EventBusConfig');
    }

    public function testEventBusConfigLoaded()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('Tsoi\EventBusBundle\EventBus\EventBusConfig', 0, 'service_container');
    }

    public function testConsumerLoaded()
    {
        $this->load();
        $this->assertContainerBuilderHasService('Tsoi\EventBusBundle\EventBus\Amqp\Consumer', 'Tsoi\EventBusBundle\EventBus\Amqp\Consumer');
    }

    public function testPublisherLoaded()
    {
        $this->load();
        $this->assertContainerBuilderHasService('Tsoi\EventBusBundle\EventBus\Amqp\Publisher', 'Tsoi\EventBusBundle\EventBus\Amqp\Publisher');
    }

    public function testRunCommandLoaded()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.command.run', 0, 'tsoi.event_bus');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('tsoi.command.run', 1, 'service_container');
    }

    protected function getContainerExtensions()
    {
        return [
            new TsoiEventBusExtension(),
        ];
    }

}