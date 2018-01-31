<?php
namespace SymfonyRollbarBundle\Tests\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use Tests\Fixtures\ErrorHandler;

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

        $listener = new \SymfonyRollbarBundle\EventListener\ExceptionListener($container);
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
}
