<?php
namespace SymfonyRollbarBundle\Tests\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ExceptionListener;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use SymfonyRollbarBundle\Tests\Fixtures\ErrorHandler;
use SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeException;

/**
 * Class ExceptionListenerTest
 * @package SymfonyRollbarBundle\Tests\EventListener
 * @runTestsInSeparateProcesses
 */
class ExceptionListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        // hack for Symfony 2.8 and ERRORS
        if (2 === Kernel::MAJOR_VERSION && Kernel::MINOR_VERSION >= 8) {
            $_SERVER['argv'] = [
                './vendor/bin/phpunit',
                '-c',
                'phpunit.xml',
            ];
        }

        static::bootKernel();
    }

    /**
     * @dataProvider generateEventExceptions
     * @param \Exception $expected
     */
    public function testException($expected)
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners       = $eventDispatcher->getListeners('kernel.exception');
        $rbHandler       = new RollbarHandler($container);
        $event           = new GetResponseForExceptionEvent(
            static::$kernel,
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $expected
        );

        $handler = new ErrorHandler();
        $handler->setAssert(function ($record) use ($expected, $rbHandler) {
            $this->assertNotEmpty($record);

            $this->assertNotEmpty($record['context']['exception']);
            $exception = $record['context']['exception'];
            if ($rbHandler->shouldSkip($expected)) {
                return;
            }

            $this->assertEquals($expected->getMessage(), $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);

            $this->assertInstanceOf(\Exception::class, $exception);
        });

        foreach ($listeners as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener) {
                // disable default symfony listeners
                $eventDispatcher->removeListener('kernel.exception', $listener);
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        $eventDispatcher->dispatch('kernel.exception', $event);
        restore_error_handler();
    }

    public function generateEventExceptions()
    {
        return [
            [new \Exception('This is new exception')],
            [new \Exception('This is one more new exception')],
        ];
    }

    /**
     * @dataProvider generatorRandomParams
     * @param $data
     */
    public function testInvalidHandleParams($data)
    {
        $container = static::$kernel->getContainer();

        $handler = new ErrorHandler();
        $handler->setAssert(function ($record) {
            $this->assertNotEmpty($record);

            $this->assertEquals('Undefined exception', $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);
            $this->assertNotEmpty($record['context']['exception']);

            $exception = $record['context']['exception'];
            $this->assertInstanceOf(\Exception::class, $exception);
        });

        $listener = new ExceptionListener($container);
        $listener->getLogger()->setHandlers([$handler]);

        $listener->handleException($data);
    }

    /**
     * @return array
     */
    public function generatorRandomParams()
    {
        return [
            ['strange line'],
            [null],
            [['a' => 'b']],
            [(object)['a' => 'b']],
        ];
    }

    public function testSkipException()
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners       = $eventDispatcher->getListeners('kernel.exception');
        $event           = new GetResponseForExceptionEvent(
            static::$kernel,
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new MyAwesomeException("Hello!")
        );

        $handler = new ErrorHandler();
        $handler->setAssert(function ($record) {
            $this->fail("This should be newer called!");
        });

        foreach ($listeners as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener) {
                // disable default symfony listeners
                $eventDispatcher->removeListener('kernel.exception', $listener);
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        $eventDispatcher->dispatch('kernel.exception', $event);
        $this->assertTrue(true); // trick to mark not risky
        restore_error_handler();
    }

    /**
     * @dataProvider generatorHandleParams
     * @param \Throwable $data
     */
    public function testHandleParams($data)
    {
        $container = static::$kernel->getContainer();

        $handler = new ErrorHandler();
        $handler->setAssert(function ($record) use ($data) {
            $this->assertNotEmpty($record);

            $this->assertEquals($data->getMessage(), $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);
            $this->assertNotEmpty($record['context']['exception']);

            $exception = $record['context']['exception'];
            $this->assertEquals($data, $exception);
        });

        $listener = new ExceptionListener($container);
        $listener->getLogger()->setHandlers([$handler]);

        $listener->handleException($data);
    }

    public function generatorHandleParams()
    {
        return [
            [new \Exception('This is new exception')],
            [new \Exception('This is one more new exception')],
            [new \TypeError('This is TypeError')],
            [new \DivisionByZeroError('This is DivisionByZeroError')],
        ];
    }
}
