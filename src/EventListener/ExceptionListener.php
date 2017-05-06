<?php

namespace SymfonyRollbarBundle\EventListener;

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
    protected function handleException($exception)
    {
        // generate payload and log data
        list($message, $payload) = $this->getGenerator()->getExceptionPayload($exception);

        $this->getLogger()->error($message, [
            'payload' => $payload,
        ]);
    }
}
