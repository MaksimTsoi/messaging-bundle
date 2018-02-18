<?php

namespace Tsoi\EventBusBundle\EventBus\Events;

use Ramsey\Uuid\Uuid;

/**
 * Class IntegrationEvent
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
     * @var string
     */
    protected $routing;

    /**
     * @var string
     */
    protected $queue;

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
     * @return mixed
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->queue;
    }
}