<?php

namespace Tsoi\EventBusBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Tsoi\EventBusBundle\DependencyInjection\Configuration;

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
     * RunCommand constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $integrationEvents = $this->container->getParameter(
            'tsoi_event_bus'
        )['microservices'][Configuration::CURRENT_MS]['integration_events'];

        foreach ($integrationEvents as $integrationEvent) {
            $process = new Process(
                sprintf(
                    'php bin/console tsoi_event_bus:subscribe %s %s',
                    \addslashes($integrationEvent['event']),
                    \addslashes($integrationEvent['event_handler'])
                )
            );
            $process->start();
        }
    }
}