<?php

namespace Tsoi\EventBusBundle\EventBus;

use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

/**
 * Class IntegrationEventHandler
 *
 * @package Tsoi\EventBusBundle\EventBus
 */
abstract class IntegrationEventHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * IntegrationEventHandler constructor.
     *
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param IntegrationEvent $integrationEvent
     * @param array            $body
     *
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    protected function response(IntegrationEvent $integrationEvent, $body = [])
    {
        $responseEvent = $integrationEvent->getFrom();
        $responseEvent->setBody($body);

        $this->eventBus->publish($responseEvent);
    }
}