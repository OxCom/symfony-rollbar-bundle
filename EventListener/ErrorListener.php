<?php

namespace SymfonyRollbarBundle\EventListener;

use Rollbar\ErrorWrapper;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

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

        $utilities = new \Rollbar\Utilities();
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $wrapper   = new ErrorWrapper($code, $message, $file, $line, $backTrace, $utilities);

        $this->getLogger()->error($message, [
            'level'     => \Monolog\Logger::CRITICAL,
            'exception' => $wrapper,
        ]);
    }

    /**
     * Process fatal errors
     */
    public function handleFatalError()
    {
        $error = $this->getLastError();
        if (empty($error)) {
            return;
        }

        // due to PHP docs we allways will have such structure for errors
        $code    = $error['type'];
        $message = $error['message'];
        $file    = $error['file'];
        $line    = $error['line'];

        $this->handleError($code, $message, $file, $line);
    }

    /**
     * Wrap php error_get_last() to get more testable code
     * @link: http://php.net/manual/en/function.error-get-last.php
     *
     * @return array|null
     * @codeCoverageIgnore
     */
    protected function getLastError()
    {
        return error_get_last();
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
        $config = $this->getContainer()->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

        return true
            && $config['enable']
            && !(error_reporting() === 0 && $config['rollbar']['report_suppressed'])
            && !(($config['rollbar']['use_error_reporting'] && (error_reporting() & $code) === 0))
            && !($config['rollbar']['included_errno'] != -1 && ($code & $config['rollbar']['included_errno']) != $code);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // dummy
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleErrorEvent
     *        |\Symfony\Component\Console\Event\ConsoleExceptionEvent $event
     */
    public function onConsoleError($event)
    {
        // dummy
    }
}
