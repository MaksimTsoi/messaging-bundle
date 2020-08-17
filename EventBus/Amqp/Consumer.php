<?php

namespace Tsoi\EventBusBundle\EventBus\Amqp;

use Closure;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Tsoi\EventBusBundle\Exception\BreakException;

/**
 * Class Consumer
 *
 * @package Tsoi\EventBusBundle\EventBus\Amqp
 */
class Consumer extends Request
{
    /**
     * @param  string  $queue
     * @param  string|array  $routing
     * @param  Closure  $callback
     *
     * @return bool
     * @throws \ErrorException
     */
    public function consume(string $queue, $routing, Closure $callback): bool
    {
        $this->addConfig(
            [
                'queue' => [
                    'name'    => $queue,
                    'routing' => $routing,
                ],
            ]
        );
        $this->run();

        try {
            if ( ! $this->getConfig('consumer.persistent') && $this->getQueueMessageCount() == 0) {
                throw new BreakException();
            }

            $this->getChannel()->basic_qos(null, 1, null);

            $this->getChannel()->basic_consume(
                $queue,
                $this->getConfig('consumer.tag'),
                $this->getConfig('consumer.no_local'),
                $this->getConfig('consumer.no_ack'),
                $this->getConfig('consumer.exclusive'),
                $this->getConfig('consumer.nowait'),
                function ($message) use ($callback) {
                    $callback(\unserialize($message->getBody()), $message->delivery_info['routing_key']);
                    $this->acknowledge($message);
                }
            );

            while (count($this->getChannel()->callbacks)) {
                $this->getChannel()->wait(
                    null,
                    $this->getConfig('consumer.wait.non_blocking', false),
                    $this->getConfig('consumer.wait.timeout', 0)
                );
            }
        } catch (BreakException | AMQPTimeoutException $e) {
            return true;
        }

        return true;
    }

    /**
     * @return void
     */
    public function cancel():void
    {
        $this->getChannel()->basic_cancel($this->getConfig('consumer.tag'));
    }

    /**
     * @param  mixed  $message
     *
     * @return void
     */
    private function acknowledge($message): void
    {
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }
}