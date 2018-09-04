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
     * @param string  $queue
     * @param string  $routing
     * @param Closure $callback
     *
     * @throws BreakException
     *
     * @return bool
     */
    public function consume(string $queue, string $routing, Closure $callback)
    {
        $this->addConfig(
            [
                'queue' => [
                    'name'    => $queue,
                    'routing' => $routing,
                ],
            ]
        )->run();

        try {
            if (!$this->getConfig('consumer.persistent') && $this->getQueueMessageCount() == 0) {
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
        } catch (BreakException $exception) {
            return true;
        } catch (AMQPTimeoutException $exception) {
            return true;
        }

        return true;
    }

    /**
     * @param $message
     */
    public function acknowledge($message)
    {
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }

    /**
     * Rejects a message and requeues it if wanted (default: false).
     *
     * @param Message $message
     * @param bool    $requeue
     */
    public function reject($message, $requeue = false)
    {
        $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], $requeue);
    }
}