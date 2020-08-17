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
    private $uuid;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $trace;

    /**
     * @var IntegrationEvent
     */
    private $from;

    /**
     * IntegrationEvent constructor.
     */
    public function __construct()
    {
        $this->uuid         = Uuid::uuid1()->toString();
        $this->creationDate = new \DateTime();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime
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
     * @param  IntegrationEvent  $event
     *
     * @return IntegrationEvent
     */
    public function addTrace(IntegrationEvent $event): IntegrationEvent
    {
        $this->trace[] = $event;

        return $this;
    }
}