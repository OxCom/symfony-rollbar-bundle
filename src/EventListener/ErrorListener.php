<?php

namespace SymfonyRollbarBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ErrorListener extends AbstractListener
{
    /**
     * ErrorListener constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        // here only errors, so we have to setup handler here
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Handle error
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return void
     */
    public function handleError($code, $message, $file, $line)
    {
        if (!$this->isReportable($code)) {
            return;
        }

        list($message, $payload) = $this->getGenerator()->getErrorPayload($code, $message, $file, $line);

        $this->getLogger()->error($message, [
            'payload' => $payload,
        ]);
    }

    /**
     * Process fatal errors
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        if (empty($error)) {
            return;
        }

        $code    = $error['type'];
        $message = $error['message'];
        $file    = $error['file'];
        $line    = $error['line'];

        $this->handleError($code, $message, $file, $line);
    }

    /**
     * Check do we need to report error or skip
     *
     * @param $code
     *
     * @return int
     */
    protected function isReportable($code)
    {
        $code = (int)$code;

        return error_reporting() & $code !== 0;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // dummy
    }
}
