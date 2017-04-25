<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;

class Error extends \SymfonyRollbarBundle\EventListener\AbstractListener
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
//            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
    }
}
