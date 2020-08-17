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
     * @return void
     */
    public function publish(string $routing, AMQPMessage $message): void
    {
        $this->run();

        $this->getChannel()->basic_publish($message, $this->getConfig('exchange.name'), $routing);
    }
}