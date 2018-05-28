<?php
namespace SymfonyRollbarBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ErrorListener;
use SymfonyRollbarBundle\EventListener\ExceptionListener;

/**
 * Class AbstractListenerTest
 * @package SymfonyRollbarBundle\Tests\EventListener
 */
class AbstractListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    /**
     * @return array
     */
    public function generatorGetSubscribedEvents()
    {
        return [
            [ErrorListener::class],
            [ExceptionListener::class],
        ];
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetSubscribedEvents($class)
    {
        $handler = set_error_handler('var_dump');
        restore_error_handler();

        /**
         * @var AbstractListener $listener
         */
        $container = static::$kernel->getContainer();
        $listener  = new $class($container);

        $expect = [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
        $list = $listener::getSubscribedEvents();

        $this->assertEquals($expect, $list);
        set_error_handler($handler);
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetLogger($class)
    {
        $handler = set_error_handler('var_dump');
        restore_error_handler();

        /**
         * @var AbstractListener $listener
         */
        $container = static::$kernel->getContainer();
        $listener  = new $class($container);

        $logger = $listener->getLogger();
        $this->assertTrue($logger instanceof \Monolog\Logger);
        set_error_handler($handler);
    }
}
