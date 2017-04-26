<?php
namespace Tests\SymfonyRollbarBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BundleTest
 * @package Tests\SymfonyRollbarBundle
 */
class BundleTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testException()
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $exception       = new \Exception('This is new exception');
        $event           = new GetResponseForExceptionEvent(
            static::$kernel, new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $eventDispatcher->dispatch('kernel.exception', $event);
    }

    public function testError()
    {
        trigger_error("Fatal error", E_USER_ERROR);
//        $container = static::$kernel->getContainer();
//
//        /**
//         * @var \SymfonyRollbarBundle\EventListener\ErrorListener $errorHandler
//         */
//        $errorHandler = $container->get('symfony_rollbar.event_listener.error_listener');
//        $errorHandler->handleError(E_ERROR, 'This is new error', __FILE__, rand(10, 100));
    }
}
