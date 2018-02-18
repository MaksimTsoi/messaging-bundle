<?php

namespace Tsoi\EventBusBundle\EventBus\Abstractions;

use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

/**
 * Interface IntegrationEventHandler
 * @package Tsoi\EventBusBundle\EventBus\Abstractions
 */
interface IntegrationEventHandler
{
    /**
     * @param IntegrationEvent $event
     *
     * @return mixed
     */
    public function handle(IntegrationEvent $event);
}