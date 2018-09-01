<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\DynamicIntegrationEventHandler;
use Tsoi\EventBusBundle\EventBus\Abstractions\EventBusInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandler;
use Tsoi\EventBusBundle\EventBus\Amqp\Consumer;
use Tsoi\EventBusBundle\EventBus\Amqp\Message;
use Tsoi\EventBusBundle\EventBus\Amqp\Publisher;
use Tsoi\EventBusBundle\EventBus\Amqp\Request;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

/**
 * Class EventBus
 *
 * @package Tsoi\EventBusBundle\EventBus
 */
class EventBus implements EventBusInterface
{
    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Tsoi\EventBusBundle\EventBus\EventBusConfig
     */
    protected $config;

    /**
     * EventBus constructor.
     *
     * @param \Tsoi\EventBusBundle\EventBus\Amqp\Publisher              $publisher
     * @param \Tsoi\EventBusBundle\EventBus\Amqp\Consumer               $consumer
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Tsoi\EventBusBundle\EventBus\EventBusConfig              $config
     */
    public function __construct(
        Publisher $publisher,
        Consumer $consumer,
        ContainerInterface $container,
        EventBusConfig $config
    ) {
        $this->publisher = $publisher;
        $this->consumer  = $consumer;
        $this->container = $container;
        $this->config    = $config;
    }

    /**
     * @param \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent $integrationEvent
     *
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function publish(IntegrationEvent $integrationEvent)
    {
        foreach ($this->config->get($integrationEvent) as $config) {
            $this->publisher->addConfig($config);
            $this->publisher->publish(
                $integrationEvent->getRouting(),
                new Message(\serialize($integrationEvent))
            );
        }

        Request::shutdown($this->publisher->getChannel(), $this->publisher->getConnection());
    }

    /**
     * @param \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent              $integrationEvent
     * @param \Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandler $eventHandler
     *
     * @throws \Tsoi\EventBusBundle\Exception\BreakException
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler)
    {
        foreach ($this->config->get($integrationEvent, $eventHandler) as $config) {
            $this->consumer->addConfig($config);
            $this->consumer->consume(
                $integrationEvent->getQueue(),
                $integrationEvent->getRouting(),
                function (IntegrationEvent $event) use ($eventHandler) {
                    $eventHandler->handle($event);
                }
            );
        }

        Request::shutdown($this->consumer->getChannel(), $this->consumer->getConnection());
    }

    /**
     * @inheritdoc
     */
    public function unSubscribe(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler)
    {
    }

    /**
     * @inheritdoc
     */
    public function subscribeDynamic(string $eventName, DynamicIntegrationEventHandler $eventHandler)
    {
    }

    /**
     * @inheritdoc
     */
    public function unSubscribeDynamic(string $eventName, DynamicIntegrationEventHandler $eventHandler)
    {
    }
}