TsoiEventBusBundle
==================

TsoiEventBusBundle is event-based communication between microservices for Symfony4. It is based on [RabbitMQ](https://www.rabbitmq.com) and uses library [php-amqplib/php-amqplib](https://github.com/php-amqplib/php-amqplib).

Installation
------------

### 1. Use Composer to download:

```bash
  $ composer require tsoi/event-bus-bundle
```

### 2. Register the bundle in your application:

Update file `config/bundles.php`
```php
return [
    Tsoi\EventBusBundle\TsoiEventBusBundle::class => ['all' => true],
]
```

### 3. Configure

Create file `config/packages/tsoi_event_bus.yaml` in first microservice.
```yml
tsoi_event_bus:
    microservices:
        current_microservice:
            connection:
                host: localhost
                port: 5672
                user_name: guest
                password: guest
            exchange:
                name: event_bus1
            integration_events:
                - event: 'App\IntegrationEvents\Events\SomeEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\SomeEventHandle'
                - event: 'App\IntegrationEvents\Events\AnotherEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\AnotherEvent'
        microservice2:
            connection:
                host: localhost
                port: 5672
                user_name: guest
                password: guest
            exchange:
                name: event_bus2
            integration_events:
                - event: 'App\IntegrationEvents\Events\SomeEvent'
                - event: 'App\IntegrationEvents\Events\AnotherEvent'
```

Create file `config/packages/tsoi_event_bus.yaml` in second microservice.
```yml
tsoi_event_bus:
    microservices:
        current_microservice:
            connection:
                host: localhost
                port: 5672
                user_name: guest
                password: guest
            exchange:
                name: event_bus2
            integration_events:
                - event: 'App\IntegrationEvents\Events\SomeEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\SomeEventHandle'
                - event: 'App\IntegrationEvents\Events\AnotherEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\AnotherEvent'
        microservice1:
            connection:
                host: localhost
                port: 5672
                user_name: guest
                password: guest
            exchange:
                name: event_bus1
            integration_events:
                - event: 'App\IntegrationEvents\Events\SomeEvent'
                - event: 'App\IntegrationEvents\Events\AnotherEvent'
``` 

Update file `config/services.yaml`
```yml
services:
    App\IntegrationEvents\:
        resource: '../src/IntegrationEvents/*'
        autowire: true
        autoconfigure: true
        public: true
``` 

How to use
----------
