<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use SymfonyRollbarBundle\Payload\Generator;

class ExceptionListener extends AbstractListener
{
    /**
     * Process exception
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof \Exception) {
            // generate payload and log data
            list($message, $payload) = $this->getGenerator()->getExceptionPayload($exception);

            $this->getLogger()->error($message, [
                'payload' => $payload,
            ]);
        }
    }
}
