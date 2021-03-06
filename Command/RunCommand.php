<?php

namespace Tsoi\EventBusBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\DependencyInjection\Configuration;
use Tsoi\EventBusBundle\EventBus\EventBus;
use Tsoi\EventBusBundle\Exception\ConfigException;

/**
 * Class RunCommand
 * @package Tsoi\EventBusBundle\Command
 */
class RunCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * RunCommand constructor.
     *
     * @param  EventBus  $eventBus
     * @param  ContainerInterface  $container
     */
    public function __construct(EventBus $eventBus, ContainerInterface $container)
    {
        $this->eventBus  = $eventBus;
        $this->container = $container;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('tsoi_event_bus:run')
            ->setDescription('Run microservice.')
            ->setHelp('This command allows you to run microservice');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     *
     * @return int|void|null
     * @throws \ErrorException
     * @throws ConfigException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters        = $this->container->getParameter('tsoi_event_bus');
        $currentMS         = $parameters[Configuration::CURRENT_MS];
        $integrationEvents = $parameters['microservices'][$currentMS]['integration_events'];

        foreach ($integrationEvents as $integrationEvent) {
            $this->eventBus->subscribe(
                $this->container->get($integrationEvent['event']),
                $this->container->get($integrationEvent['event_handler'])
            );
        }

        $this->eventBus->execute();
    }
}