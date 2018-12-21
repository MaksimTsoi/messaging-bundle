<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\DynamicIntegrationEventHandlerInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\EventBusInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface;
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
     * @var array
     */
    protected $subscriptions = [];

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
        $this->config->setIntegrationEvent($integrationEvent);
        $this->publisher->addConfig($this->config->get());
        $this->publisher->publish($this->config->getRoutingName(), new Message(\serialize($integrationEvent)));

        Request::shutdown($this->publisher->getChannel(), $this->publisher->getConnection());
    }

    /**
     * @param \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent                       $integrationEvent
     * @param \Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface $eventHandler
     *
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler)
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
     * @throws \Tsoi\EventBusBundle\Exception\BreakException
     */
    public function execute()
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
     * @inheritdoc
     */
    public function unSubscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler)
    {
    }

    /**
     * @inheritdoc
     */
    public function subscribeDynamic(string $eventName, DynamicIntegrationEventHandlerInterface $eventHandler)
    {
    }

    /**
     * @inheritdoc
     */
    public function unSubscribeDynamic(string $eventName, DynamicIntegrationEventHandlerInterface $eventHandler)
    {
    }
}