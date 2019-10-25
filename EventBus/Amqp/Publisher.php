<?php

namespace Tsoi\EventBusBundle\EventBus\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Publisher
 * @package Tsoi\EventBusBundle\EventBus\Amqp
 */
class Publisher extends Request
{
    /**
     * @param string $routing
     * @param AMQPMessage $message
     *
     * @throws \Tsoi\EventBusBundle\Exception\ConfigException
     */
    public function publish(string $routing, AMQPMessage $message)
    {
        $this->run();

        $this->getChannel()->basic_publish($message, $this->getConfig('exchange.name'), $routing);
    }
}