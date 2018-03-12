<?php

namespace Tsoi\EventBusBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tsoi\EventBusBundle\EventBus\EventBus;

/**
 * Class SubscribeCommand
 * @package Tsoi\EventBusBundle\Command
 */
class SubscribeCommand extends Command
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
     * SubscribeCommand constructor.
     *
     * @param EventBus           $eventBus
     * @param ContainerInterface $container
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
            ->setName('tsoi_event_bus:subscribe')
            ->setDescription('Subscribe event.')
            ->setHelp('This command allows you to subscribe event')
            ->addArgument('event', InputArgument::REQUIRED, 'Event name')
            ->addArgument('event_handler', InputArgument::REQUIRED, 'Event handler name');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event        = $this->container->get($input->getArgument('event'));
        $eventHandler = $this->container->get($input->getArgument('event_handler'));

        $this->eventBus->subscribe($event, $eventHandler);
    }
}