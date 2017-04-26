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
        // here only errors, so we have to setup handler here
        $h = set_error_handler([$this, 'handleError']);

        var_dump($h);die;

        return [];
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        return false;
    }
}
