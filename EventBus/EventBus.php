<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\DependencyInjection\Configuration;
use Tsoi\EventBusBundle\EventBus\Abstractions\DynamicIntegrationEventHandler;
use Tsoi\EventBusBundle\EventBus\Abstractions\EventBusInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandler;
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
     * EventBus constructor.
     *
     * @param Publisher          $publisher
     * @param Consumer           $consumer
     * @param ContainerInterface $container
     */
    public function __construct(Publisher $publisher, Consumer $consumer, ContainerInterface $container)
    {
        $this->publisher = $publisher;
        $this->consumer  = $consumer;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function publish(IntegrationEvent $integrationEvent)
    {
        foreach ($this->getConfigs($integrationEvent) as $config) {
            if ($config) {
                $this->publisher->addConfig($config);
                $this->publisher->publish(
                    $integrationEvent->getRouting(),
                    new Message(\serialize($integrationEvent))
                );
                Request::shutdown($this->publisher->getChannel(), $this->publisher->getConnection());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler)
    {
        foreach ($this->getConfigs($integrationEvent, $eventHandler) as $config) {
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

    /**
     * @param IntegrationEvent             $integrationEvent
     * @param IntegrationEventHandler|null $eventHandler
     *
     * @return array
     * @throws ConfigException
     */
    private function getConfigs(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler = null)
    {
        if (!$this->container->hasParameter('tsoi_event_bus')) {
            throw new ConfigException("Please check your config file, parameter 'tsoi_event_bus' is not defined.");
        }

        $key                 = \get_class($integrationEvent);
        $microservicesConfig = $this->container->getParameter('tsoi_event_bus')['microservices'];

        if ($eventHandler) {
            $currentMicroserviceConfig = $microservicesConfig[Configuration::CURRENT_MS];
            $integrationEvents         = $currentMicroserviceConfig['integration_events'];
            $event                     = array_column($integrationEvents, 'event');

            if (!in_array($key, $event, true)) {
                throw new ConfigException(
                    \sprintf("Please check your config file, event '%s' is not defined.", $key)
                );
            }

            $eventHandling = array_column($integrationEvents, 'event_handler');

            if (!in_array(\get_class($eventHandler), $eventHandling, true)) {
                throw new ConfigException(
                    \sprintf("Please check your config file, event handler '%s' is not defined.", $eventHandler)
                );
            }

            return [$currentMicroserviceConfig];
        }

        $result = \array_map(function($item) use ($key) {
            $event = array_column($item['integration_events'], 'event');

            if (in_array($key, $event, true)) {
                return $item;
            }
        }, $microservicesConfig);

        if (empty($result)) {
            throw new ConfigException(\sprintf("Please check your config file, event '%s' is not defined.", $key));
        }

        return $result;
    }
}