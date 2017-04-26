<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
            $generator = new Generator($this->container);
            $request = $event->getRequest();

            list($message, $payload) = $generator->getPayload($exception, $request);

            $this->getLogger()->error($message, [
                'payload' => $payload,
            ]);
        }
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
