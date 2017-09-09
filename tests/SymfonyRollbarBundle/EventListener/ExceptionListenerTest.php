<?php
namespace Tests\SymfonyRollbarBundle\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use SymfonyRollbarBundle\EventListener\AbstractListener;

/**
 * Class ExceptionListenerTest
 * @package Tests\SymfonyRollbarBundle\EventListener
 */
class ExceptionListenerTest extends KernelTestCase
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
            static::$kernel,
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $eventDispatcher->dispatch('kernel.exception', $event);
    }
}
