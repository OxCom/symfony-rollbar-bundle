<?php
namespace Tests\SymfonyRollbarBundle\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ErrorListener;

/**
 * Class ErrorListenerTest
 * @package Tests\SymfonyRollbarBundle\EventListener
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
            $this->assertTrue(!empty($record['context']['payload']['body']['trace']['exception']['class']));

            $trace = $record['context']['payload']['body']['trace'];
            $this->assertEquals('E_USER_ERROR', $trace['exception']['class']);
            $this->assertNotEmpty($trace['frames']);
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
    }

    /**
     * @runInSeparateProcess
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
                $this->assertTrue(!empty($record['context']['payload']['body']['trace']['exception']['class']));

                $trace = $record['context']['payload']['body']['trace'];
                $this->assertEquals('E_ERROR', $trace['exception']['class']);
                $this->assertNotEmpty($trace['frames']);
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
            if (!$listener[0] instanceof ErrorListener) {
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        // @ will allow to skip fatal error inside application, but we can get error with error_get_last()
        @include __DIR__ . '/../../Fixtures/fatal.php';
    }
}
