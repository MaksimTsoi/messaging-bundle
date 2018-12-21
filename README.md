TsoiEventBusBundle
==================
[![Release](https://img.shields.io/packagist/v/tsoi/event-bus-bundle.svg)](https://packagist.org/packages/tsoi/event-bus-bundle)
[![License](http://img.shields.io/packagist/l/tsoi/event-bus-bundle.svg)](https://packagist.org/packages/tsoi/event-bus-bundle)

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

Create file `config/packages/tsoi_event_bus.yaml` in **first** microservice.
```yml
tsoi_event_bus:
    default_connection:
        host: rabbitmq
        port: 5672
        user_name: guest
        password: guest
    current_microservice: microservice1
    microservices:
        microservice1:
        microservice2:
```

Create file `config/packages/tsoi_event_bus.yaml` in **second** microservice.
```yml
tsoi_event_bus:
    default_connection:
        host: rabbitmq
        port: 5672
        user_name: guest
        password: guest
    current_microservice: microservice2
    microservices:
        microservice1:
        microservice2:
``` 

Update file `config/services.yaml` in **each** microservice.
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

### First microservice:

Create event `src/IntegrationEvents/Events/HelloEvent.php`

```php
<?php

namespace App\IntegrationEvents\Events;

use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

class HelloEvent extends IntegrationEvent
{
}
```

Create event handler `src/IntegrationEvents/EventHandling/HelloEventHandler.php`

```php
<?php

namespace App\IntegrationEvents\EventHandling;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\EventBus\Abstractions\IntegrationEventHandlerInterface;
use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

class HelloEventHandler implements IntegrationEventHandlerInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(IntegrationEvent $event)
    {
        $log = $this->container->get('kernel')->getLogDir().'/HelloEvent';
        file_put_contents($log, $event->getBody()[0], FILE_APPEND);
    }
}
```

Update `config/packages/tsoi_event_bus.yaml`. Add **event**, **event_handler** in `integration_events`

```yml
tsoi_event_bus:
    microservices:
        microservice1:
            integration_events:
                - event: 'App\IntegrationEvents\Events\HelloEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\HelloEventHandler'
``` 

**Subscribe** to events. Run the command

```bash
$ php bin/console tsoi_event_bus:run
```

Or run supervisord

```supervisord
[program:tsoi_event_bus]
command=php /home/$user/symfony_project/bin/console tsoi_event_bus:run
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
user=$user
numprocs=1
redirect_stderr=true
stdout_logfile=/home/$user/symfony_project/var/log/tsoi_event_bus.log
```

### Second microservice:

Create event `src/IntegrationEvents/Events/HelloEvent.php` is same as in first microservice.

```php
<?php

namespace App\IntegrationEvents\Events;

use Tsoi\EventBusBundle\EventBus\Events\IntegrationEvent;

class HelloEvent extends IntegrationEvent
{
}
```

Update `config/packages/tsoi_event_bus.yaml` is same as in first microservice.

```yml
tsoi_event_bus:
    microservices:
        microservice1:
            integration_events:
                - event: 'App\IntegrationEvents\Events\HelloEvent'
                  event_handler: 'App\IntegrationEvents\EventHandling\HelloEventHandler'
``` 

**Publish** event. Update `src/Controller/DefaultController.php`

```php
<?php

namespace App\Controller;

use App\IntegrationEvents\Events\HelloEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $eventBus   = $this->get('tsoi.event_bus');
        $helloEvent = new HelloEvent();
        $helloEvent->setBody(['hello world!']);
        $eventBus->publish($helloEvent);
    }
}
```

Now check your log `var/log/HelloEvent` in first microservice.