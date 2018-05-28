<?php
namespace SymfonyRollbarBundle\Tests;

use Symfony\Bridge\PhpUnit\DeprecationErrorHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BundleTest
 * @package SymfonyRollbarBundle\Tests
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
        $listeners       = $eventDispatcher->getListeners('kernel.exception');

        $expected = [
            \SymfonyRollbarBundle\EventListener\ErrorListener::class,
            \SymfonyRollbarBundle\EventListener\ExceptionListener::class,
        ];

        foreach ($expected as $class) {
            $found = false;
            foreach ($listeners as $listener) {
                if ($listener[0] instanceof $class) {
                    $found = true;
                    break;
                }
            }

            $this->assertTrue($found, "Listeners {$class} was not registered");
        }

        restore_error_handler();
    }
}
