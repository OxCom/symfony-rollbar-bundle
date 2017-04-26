<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;

class ErrorListener extends AbstractListener
{
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
