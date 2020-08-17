<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\EventBusInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface;
use Tsoi\EventBusBundle\EventBus\Amqp\Consumer;
use Tsoi\EventBusBundle\EventBus\Amqp\Message;
use Tsoi\EventBusBundle\EventBus\Amqp\Publisher;
use Tsoi\EventBusBundle\EventBus\Amqp\Request;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;
use Tsoi\EventBusBundle\Exception\ConfigException;

/**
 * Class EventBus
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
     * @var EventBusConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $subscriptions = [];

    /**
     * EventBus constructor.
     *
     * @param  Publisher  $publisher
     * @param  Consumer  $consumer
     * @param  ContainerInterface  $container
     * @param  EventBusConfig  $config
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
     * @param  IntegrationEvent  $integrationEvent
     *
     * @throws ConfigException
     */
    public function publish(IntegrationEvent $integrationEvent): void
    {
        $this->config->setIntegrationEvent($integrationEvent);
        $this->config->removeEventHandler();
        $this->publisher->addConfig($this->config->get());
        $this->publisher->publish($this->config->getRoutingName(), new Message(\serialize($integrationEvent)));

        Request::shutdown($this->publisher->getChannel(), $this->publisher->getConnection());
    }

    /**
     * @param  IntegrationEvent  $integrationEvent
     * @param  IntegrationEventHandlerInterface  $eventHandler
     *
     * @return void
     * @throws ConfigException
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler): void
    {
        $this->config->setIntegrationEvent($integrationEvent)
                     ->setEventHandler($eventHandler);
        $routingName = $this->config->getRoutingName();

        $this->subscriptions['config']                     = $this->config->get();
        $this->subscriptions['queueName']                  = $this->config->getQueueName();
        $this->subscriptions['routingNames'][]             = $routingName;
        $this->subscriptions['eventHandler'][$routingName] = $eventHandler;
    }

    /**
     * @return void
     * @throws \ErrorException
     */
    public function execute(): void
    {
        if (empty($this->subscriptions)) {
            return;
        }

        $this->consumer->addConfig($this->subscriptions['config']);
        $eventHandlers = $this->subscriptions['eventHandler'];
        $this->consumer->consume(
            $this->subscriptions['queueName'],
            $this->subscriptions['routingNames'],
            function (IntegrationEvent $event, $routingName) use ($eventHandlers) {
                $eventHandlers[$routingName]->handle($event);
            }
        );

        Request::shutdown($this->consumer->getChannel(), $this->consumer->getConnection());
    }

    /**
     * @return void
     * @inheritdoc
     */
    public function unSubscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler): void
    {
        $this->consumer->cancel();
    }
}