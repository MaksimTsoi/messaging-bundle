<?php

namespace Tsoi\EventBusBundle\EventBus\Events;

use Ramsey\Uuid\Uuid;

/**
 * Class IntegrationEvent
 *
 * @package Tsoi\EventBusBundle\EventBus\Events
 */
class IntegrationEvent
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var int
     */
    protected $creationDate;

    /**
     * @var array
     */
    protected $body;

    /**
     * @var array
     */
    protected $trace;

    /**
     * @var IntegrationEvent
     */
    protected $from;

    /**
     * IntegrationEvent constructor.
     */
    public function __construct()
    {
        $this->uuid         = Uuid::uuid1()->toString();
        $this->creationDate = time();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param array $body
     *
     * @return IntegrationEvent
     */
    public function setBody(array $body): IntegrationEvent
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return IntegrationEvent
     */
    public function getFrom():IntegrationEvent
    {
        return $this->from;
    }

    /**
     * @param IntegrationEvent $event
     *
     * @return IntegrationEvent
     */
    public function setFrom(IntegrationEvent $event): IntegrationEvent
    {
        $this->from = $event;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrace(): array
    {
        return $this->trace;
    }

    /**
     * @param \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent $event
     *
     * @return \Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent
     */
    public function addTrace(IntegrationEvent $event): IntegrationEvent
    {
        $this->trace[] = $event;

        return $this;
    }
}