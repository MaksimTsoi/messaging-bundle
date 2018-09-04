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
     * @var string
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
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
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