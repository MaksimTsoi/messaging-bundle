<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\DependencyInjection\Configuration;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;
use Tsoi\EventBusBundle\Exception\ConfigException;

/**
 * Class EventBusConfig
 * @package Tsoi\EventBusBundle\EventBus
 */
class EventBusConfig
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var IntegrationEvent
     */
    protected $integrationEvent;
    /**
     * @var IntegrationEventHandlerInterface
     */
    protected $eventHandler;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * EventBusConfig constructor.
     *
     * @param  ContainerInterface  $container
     *
     * @throws ConfigException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if ( ! $this->container->hasParameter('tsoi_event_bus')) {
            throw new ConfigException("Please check your config file, parameter 'tsoi_event_bus' is not defined.");
        }

    }

    /**
     * @param  IntegrationEvent  $integrationEvent
     *
     * @return EventBusConfig
     */
    public function setIntegrationEvent(IntegrationEvent $integrationEvent): EventBusConfig
    {
        $this->integrationEvent = $integrationEvent;
        $this->data             = [];

        return $this;
    }

    /**
     * @param  IntegrationEventHandlerInterface  $eventHandler
     *
     * @return EventBusConfig
     */
    public function setEventHandler(IntegrationEventHandlerInterface $eventHandler): EventBusConfig
    {
        $this->eventHandler = $eventHandler;
        $this->data         = [];

        return $this;
    }

    /**
     * @return EventBusConfig
     */
    public function removeEventHandler(): EventBusConfig
    {
        $this->eventHandler = null;

        return $this;
    }

    /**
     * @return array
     * @throws ConfigException
     */
    public function get(): array
    {
        if ( ! empty($this->data)) {
            return $this->data;
        }

        $key                 = \get_class($this->integrationEvent);
        $config              = $this->container->getParameter('tsoi_event_bus');
        $microservicesConfig = $config['microservices'];
        $currentMicroservice = $config[Configuration::CURRENT_MS];

        if ($this->eventHandler) {
            $microservicesConfig = [$currentMicroservice => $microservicesConfig[$currentMicroservice]];
        }

        $result = \array_filter(
            \array_map(
                function ($item) use ($key) {
                    $event = \array_column($item['integration_events'], 'event');

                    if (\in_array($key, $event, true)) {
                        return $item;
                    }

                    return null;
                },
                $microservicesConfig
            )
        );

        if (empty($result)) {
            throw new ConfigException(\sprintf("Please check your config file, event '%s' is not defined.", $key));
        }

        $this->data                     = \current($result);
        $exchangeName                   =
            isset($this->data['exchange']['name']) ? $this->data['exchange']['name'] : \key($result);
        $this->data['exchange']['name'] = 'tsoi-'.$exchangeName;

        if ( ! isset($this->data['connection'])) {
            $this->data['connection'] = $config['default_connection'];
        }

        if ($this->eventHandler) {
            $eventHandling = \array_column($this->data['integration_events'], 'event_handler');

            if ( ! \in_array(\get_class($this->eventHandler), $eventHandling, true)) {
                throw new ConfigException(
                    \sprintf("Please check your config file, event handler '%s' is not defined.", $this->eventHandler)
                );
            }
        }

        return $this->data;
    }

    /**
     * @return string
     * @throws ConfigException
     */
    public function getQueueName(): string
    {
        return $this->get()['exchange']['name'].'-queue';
    }

    /**
     * @return string
     * @throws ConfigException
     */
    public function getRoutingName(): string
    {
        return $this->get()['exchange']['name'].'-'.\md5(\get_class($this->integrationEvent));
    }
}