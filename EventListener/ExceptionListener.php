<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener extends AbstractListener
{
    /**
     * ErrorListener constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Process exception
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof \Exception
            || (version_compare(PHP_VERSION, '7.0.0') >= 0 && $exception instanceof \Error)
        ) {
            $this->handleException($exception);
        }
    }

    /**
     * Handle provided exception
     *
     * @param $exception
     */
    public function handleException($exception)
    {
        if (($exception instanceof \Exception || $exception instanceof \Throwable)
            && $this->handler->shouldSkip($exception)
        ) {
            return;
        }

        $payload = [];

        // @link http://php.net/manual/en/reserved.constants.php
        // @link http://php.net/manual/en/language.errors.php7.php
        if (!$exception instanceof \Exception && !$exception instanceof \Throwable) {
            $payload   = ['message' => @serialize($exception)];
            $exception = new \Exception('Undefined exception');
        }

        if (is_callable($exception)) {
            $payload = 'callable()';
            $exception = new \Exception('Undefined exception');
        }

        $this->getLogger()->error($exception->getMessage(), [
            'level'     => \Monolog\Logger::ERROR,
            'exception' => $exception,
            'payload'   => $payload,
        ]);
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleErrorEvent
     *        |\Symfony\Component\Console\Event\ConsoleExceptionEvent $event
     */
    public function onConsoleError($event)
    {
        if (class_exists('\Symfony\Component\Console\Event\ConsoleErrorEvent')
            && $event instanceof ConsoleErrorEvent
        ) {
            $exception = $event->getError();
            $this->handleException($exception);
        } elseif (class_exists('\Symfony\Component\Console\Event\ConsoleExceptionEvent')
            && $event instanceof ConsoleExceptionEvent
        ) {
            $exception = $event->getException();
            if ($exception instanceof \Exception
                || (version_compare(PHP_VERSION, '7.0.0') >= 0 && $exception instanceof \Error)
            ) {
                $this->handleException($exception);
            }
        }
    }
}
