<?php
namespace SymfonyRollbarBundle\Tests\EventListener;

use Monolog\Logger;
use Rollbar\ErrorWrapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ErrorListener;

/**
 * Class ErrorListenerTest
 * @package SymfonyRollbarBundle\Tests\EventListener
 * @runTestsInSeparateProcesses
 */
class ErrorListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testUserError()
    {
        $message = "Fatal error - " . time();
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners = $eventDispatcher->getListeners('kernel.exception');
        $handler = \Tests\Fixtures\ErrorHandler::getInstance();

        $handler->setAssert(function (array $record) use ($message) {
            $this->assertNotEmpty($record);

            $this->assertEquals($message, $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);
            $this->assertNotEmpty($record['context']['exception']);

            $exception = $record['context']['exception'];
            $this->assertInstanceOf(ErrorWrapper::class, $exception);
        });

        foreach ($listeners as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener) {
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        trigger_error($message, E_USER_ERROR);
        restore_error_handler();
    }

    /**
     * @covers \SymfonyRollbarBundle\EventListener\ErrorListener
     */
    public function testFatalError()
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners = $eventDispatcher->getListeners('kernel.exception');
        $handler = \Tests\Fixtures\ErrorHandler::getInstance();

        $handler->setAssert(function (array $record) {
            try {
                $this->assertNotEmpty($record);

                $this->assertEquals('Call to undefined function this_is_fatal_error()', $record['message'], '');
                $this->assertEquals(Logger::ERROR, $record['level']);
                $this->assertNotEmpty($record['context']['exception']);

                $exception = $record['context']['exception'];
                $this->assertInstanceOf(ErrorWrapper::class, $exception);
                restore_error_handler();
            } catch (\Exception $e) {
                echo implode("\n", [
                    $e->getMessage(),
                    $e->getTraceAsString()
                ]);
            }
        });

        foreach ($listeners as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener) {
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        if (version_compare(PHP_VERSION, '7.0.0')  >= 0) {
            $this->expectException('Error');
            $this->expectExceptionMessage('Call to undefined function this_is_fatal_error()');
        }

        @include __DIR__ . '/../../Fixtures/fatal.php';
    }

    /**
     * @dataProvider generateFatalError
     *
     * @param array $error
     * @param bool $called
     */
    public function testFatalErrorParser($error, $called)
    {
        $mock = $this->getMockBuilder(ErrorListener::class)
                     ->setMethods(['getLastError', 'handleError'])
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock->method('getLastError')
            ->willReturn($error);

        $mock->expects($called ? $this->once() : $this->never())
            ->method('handleError')
            ->with(
                $this->equalTo($error['type']),
                $this->stringContains($error['message']),
                $this->stringContains($error['file']),
                $this->equalTo($error['line'])
            );

        /**
         * @var ErrorListener $mock
         */
        $mock->handleFatalError();
    }

    /**
     * @return array
     */
    public function generateFatalError()
    {
        return [
            [['type' => E_ERROR, 'message' => 'Error message', 'file' => __DIR__, 'line' => rand(10, 100)], true],
            [null, false]
        ];
    }

    /**
     * @dataProvider generateIsReportable
     * @param bool $called
     */
    public function testIsReportable($called)
    {
        $logger = $this->getMockBuilder(\Monolog\Logger::class)
                       ->setMethods(['error'])
                       ->setConstructorArgs(['test-alias'])
                       ->getMock();

        $logger->method('error')
               ->willReturn(true);

        $mock = $this->getMockBuilder(ErrorListener::class)
                       ->setMethods(['isReportable', 'getLogger'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $mock->method('isReportable')
             ->willReturn($called);

        $mock->expects($called ? $this->once() : $this->never())
             ->method('getLogger')
             ->willReturn($logger);

        /**
         * @var ErrorListener $mock
         */
        $mock->handleError(E_ERROR, 'Message', __FILE__, rand(1, 10));
    }

    /**
     * @return array
     */
    public function generateIsReportable()
    {
        return [
            [true],
            [false]
        ];
    }
}
