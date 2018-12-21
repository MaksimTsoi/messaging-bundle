<?php

namespace Tsoi\EventBusBundle\EventBus\Abstractions;

use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

/**
 * Interface DynamicIntegrationEventHandler
 * @package Tsoi\EventBusBundle\EventBus\Abstractions
 */
interface DynamicIntegrationEventHandlerInterface
{
    /**
     * @param IntegrationEvent $event
     *
     * @return mixed
     */
    public function handle(IntegrationEvent $event);
}