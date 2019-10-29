<?php

namespace Tsoi\EventBusBundle\EventBus\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use Tsoi\EventBusBundle\Traits\Config;

/**
 * Class Request
 * @package Tsoi\EventBusBundle\EventBus\Amqp
 */
class Request
{
    use Config;

    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var int
     */
    protected $queueMessageCount = 0;

    public function __construct()
    {
        $this->addConfig(
            [
                'connection' => [
                    'vhost'       => '/',
                    'ssl_options' => [],
                    'options'     => [],
                ],
                'exchange'   => [
                    'arguments' => [],
                ],
                'queue'      => [
                    'data' => ['x-ha-policy' => ['S', 'all']],
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->connect();

        $exchange = $this->getConfig('exchange.name');

        $this->channel->exchange_declare(
            $exchange,
            $this->getConfig('exchange.type'),
            $this->getConfig('exchange.passive'),
            true,
            $this->getConfig('exchange.auto_delete'),
            $this->getConfig('exchange.internal'),
            $this->getConfig('exchange.nowait'),
            $this->getConfig('exchange.arguments'),
            $this->getConfig('exchange.ticket')
        );

        $queue = $this->getConfig('queue.name');

        if ( ! empty($queue) || $this->getConfig('queue.force_declare')) {
            list($queueName, $this->queueMessageCount) = $this->channel->queue_declare(
                $queue,
                $this->getConfig('queue.passive'),
                true,
                $this->getConfig('queue.exclusive'),
                $this->getConfig('queue.auto_delete'),
                $this->getConfig('queue.nowait'),
                new AMQPTable($this->getConfig('queue.data'))
            );

            if ( ! $this->getConfig('queue.nobinding')) {
                $routing = $this->getConfig('queue.routing');

                if ( ! is_array($routing)) {
                    $routing = [$routing];
                }

                foreach ($routing as $routingKey) {
                    $this->channel->queue_bind($queue ?: $queueName, $exchange, $routingKey);
                }
            }
        }

        register_shutdown_function([get_class(), 'shutdown'], $this->channel, $this->connection);
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * @return int
     */
    public function getQueueMessageCount(): int
    {
        return $this->queueMessageCount;
    }

    /**
     * @param  AMQPChannel  $channel
     * @param  AMQPStreamConnection  $connection
     *
     * @return void
     * @throws \Exception
     */
    public static function shutdown(AMQPChannel $channel, AMQPStreamConnection $connection): void
    {
        $channel->close();
        $connection->close();
    }

    /**
     * @return void
     */
    protected function connect(): void
    {
        $this->connection = new AMQPSSLConnection(
            $this->getConfig('connection.host'),
            $this->getConfig('connection.port'),
            $this->getConfig('connection.user_name'),
            $this->getConfig('connection.password'),
            $this->getConfig('connection.vhost'),
            $this->getConfig('connection.ssl_options'),
            $this->getConfig('connection.options')
        );

        $this->channel = $this->connection->channel();
    }
}
