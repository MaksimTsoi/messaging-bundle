<?php

namespace Tsoi\EventBusBundle\EventBus\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Message
 * @package Tsoi\EventBusBundle\EventBus\Amqp
 */
class Message extends AMQPMessage
{
    protected static $propertyDefinitions = [
        'content_type'        => 'shortstr',
        'content_encoding'    => 'shortstr',
        'application_headers' => 'table_object',
        'delivery_mode'       => self::DELIVERY_MODE_PERSISTENT,
        'priority'            => 'octet',
        'correlation_id'      => 'shortstr',
        'reply_to'            => 'shortstr',
        'expiration'          => 'shortstr',
        'message_id'          => 'shortstr',
        'timestamp'           => 'timestamp',
        'type'                => 'shortstr',
        'user_id'             => 'shortstr',
        'app_id'              => 'shortstr',
        'cluster_id'          => 'shortstr',
    ];
}
