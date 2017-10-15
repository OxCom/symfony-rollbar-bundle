<?php
namespace Tests\SymfonyRollbarBundle\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Fixtures\ErrorHandler;

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

    /**
     * @dataProvider generateEventExceptions
     * @param $exception
     */
    public function testException($exception)
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $event           = new GetResponseForExceptionEvent(
            static::$kernel,
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $eventDispatcher->dispatch('kernel.exception', $event);
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
        $handler->setAssert(function($record) {
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
