<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

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

    /**
     * AbstractListener constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        /**
         * @var \SymfonyRollbarBundle\Provider\RollbarHandler $rbHandler
         */
        $this->logger    = new Logger(SymfonyRollbarExtension::ALIAS);
        $this->container = $container;
        $rbHandler       = $this->getContainer()->get('symfony_rollbar.provider.rollbar_handler');

        $this->getLogger()->pushHandler($rbHandler);
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    abstract public function onKernelException(GetResponseForExceptionEvent $event);

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
