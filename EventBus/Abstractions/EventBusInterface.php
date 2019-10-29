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
    public function publish(IntegrationEvent $integrationEvent): void;

    /**
     * @param IntegrationEvent                 $integrationEvent
     * @param IntegrationEventHandlerInterface $eventHandler
     *
     * @return void
     */
    public function subscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler): void;

    /**
     * @param IntegrationEvent                 $integrationEvent
     * @param IntegrationEventHandlerInterface $eventHandler
     *
     * @return void
     */
    public function unSubscribe(IntegrationEvent $integrationEvent, IntegrationEventHandlerInterface $eventHandler): void;
}
