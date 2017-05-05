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

    public function testListeners()
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners = $eventDispatcher->getListeners('kernel.exception');

        $expected = [
            \SymfonyRollbarBundle\EventListener\AbstractListener::class,
            \Symfony\Component\HttpKernel\EventListener\ExceptionListener::class,
        ];

        foreach ($listeners as $listener) {
            $ok = $listener[0] instanceof $expected[0] || $listener[0] instanceof $expected[1];
            $this->assertTrue($ok, 'Listeners were not registered');
        }
    }
}
