<?php

namespace Tsoi\EventBusBundle\EventBus\Abstractions;

use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

/**
 * Interface EventBusInterface
 * @package Tsoi\EventBusBundle\EventBus\Abstractions
 */
interface EventBusInterface
{
    /**
     * @param IntegrationEvent $integrationEvent
     *
     * @return void
     */
    public function publish(IntegrationEvent $integrationEvent);

    /**
     * @param IntegrationEvent        $integrationEvent
     * @param IntegrationEventHandler $eventHandler
     *
     * @return void
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler);

    /**
     * @param IntegrationEvent        $integrationEvent
     * @param IntegrationEventHandler $eventHandler
     *
     * @return void
     */
    public function unSubscribe(IntegrationEvent $integrationEvent, IntegrationEventHandler $eventHandler);

    /**
     * @param string                         $eventName
     * @param DynamicIntegrationEventHandler $eventHandler
     *
     * @return void
     */
    public function subscribeDynamic(string $eventName, DynamicIntegrationEventHandler $eventHandler);

    /**
     * @param string                         $eventName
     * @param DynamicIntegrationEventHandler $eventHandler
     *
     * @return void
     */
    public function unSubscribeDynamic(string $eventName, DynamicIntegrationEventHandler $eventHandler);
}
