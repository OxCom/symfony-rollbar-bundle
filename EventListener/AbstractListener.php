<?php

namespace SymfonyRollbarBundle\EventListener;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Symfony\Component\Console\ConsoleEvents;
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
     * @var \SymfonyRollbarBundle\Provider\RollbarHandler
     */
    protected $handler;

    /**
     * @var array
     */
    protected $exclude = [];

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

        $config = $this->getContainer()->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $this->exclude = empty($config['exclude']) ? [] : $config['exclude'];

        $this->handler = $rbHandler;
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
        $events = [
            KernelEvents::EXCEPTION => ['onKernelException', -100],
        ];

        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            $key = class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')
                ? ConsoleEvents::ERROR
                : ConsoleEvents::EXCEPTION;

            $events[$key] = ['onConsoleError', -100];
        }

        return $events;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    abstract public function onKernelException(GetResponseForExceptionEvent $event);

    /**
     * @param \Symfony\Component\Console\Event\ConsoleErrorEvent
     *        |\Symfony\Component\Console\Event\ConsoleExceptionEvent $event
     */
    abstract public function onConsoleError($event);

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
