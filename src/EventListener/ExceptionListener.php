<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use SymfonyRollbarBundle\Formatter\ExceptionFormatter;

class ExceptionListener extends \SymfonyRollbarBundle\EventListener\AbstractListener
{
    /**
     * Process exception
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $formatter = new ExceptionFormatter();
        $payload   = $formatter->format($exception);

        $this->getLogger()->error($payload['message'], [
            'payload' => $payload['trace_chain'],
        ]);
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
}
