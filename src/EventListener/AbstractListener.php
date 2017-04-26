<?php

namespace SymfonyRollbarBundle\EventListener;

use Rollbar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Monolog\Logger;
use Monolog\Handler\RollbarHandler;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use SymfonyRollbarBundle\Formatter\Generator;

/**
 * Class AbstractListener
 * @package SymfonyRollbarBundle\EventListener
 */
abstract class AbstractListener implements EventSubscriberInterface
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->logger    = new Logger(SymfonyRollbarExtension::ALIAS);
        $this->container = $container;
        $config          = $this->container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

        Rollbar::init($config['rollbar']);
        $handler = new RollbarHandler(Rollbar::$instance);

//        $formatter = new ExceptionFormatter();
//        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

}
