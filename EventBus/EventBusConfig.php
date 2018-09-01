<?php

namespace Tsoi\EventBusBundle\EventBus;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\DependencyInjection\Configuration;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandler;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;
use Tsoi\EventBusBundle\Exception\ConfigException;

/**
 * Class EventBusConfig
 *
 * @package Tsoi\EventBusBundle\EventBus
 */
class EventBusConfig
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * EventBusConfig constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->hasParameter('tsoi_event_bus')) {
            throw new ConfigException("Please check your config file, parameter 'tsoi_event_bus' is not defined.");
        }

    }

    /**
     * @param \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent                   $integrationEvent
     * @param \Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandler|null $eventHandler
     *
     * @return array
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function get(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler = null)
    {
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

        $result = \array_map(
            function ($item) use ($key) {
                $event = array_column($item['integration_events'], 'event');

                if (in_array($key, $event, true)) {
                    return $item;
                }
            }, $microservicesConfig
        );

        if (empty($result)) {
            throw new ConfigException(\sprintf("Please check your config file, event '%s' is not defined.", $key));
        }

        return array_filter($result);
    }
}