<?php

namespace SymfonyRollbarBundle\Tests\SymfonyRollbarBundle\EventListener;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use SymfonyRollbarBundle\Command\DeployCommand;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ErrorListener;
use SymfonyRollbarBundle\Tests\Fixtures\ErrorHandler;

/**
 * Class ConsoleListenerTest
 *
 * @package SymfonyRollbarBundle\Tests\EventListener
 */
class ConsoleListenerTest extends KernelTestCase
{
    /**
     * @covers       \SymfonyRollbarBundle\EventListener\ExceptionListener::onConsoleError
     */
    public function testLegacyConsoleException()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $erHandler = set_error_handler('var_dump');
        restore_error_handler();

        $input  = new ArrayInput([]);
        $output = new StreamOutput(
            fopen('php://memory', 'w', false),
            OutputInterface::VERBOSITY_QUIET,
            false
        );

        $error     = new \Exception('This is console exception');
        $command   = new DeployCommand($container);

        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            if (class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')) {
                $event = new ConsoleErrorEvent($input, $output, $error, $command);
            }

            if (class_exists('\Symfony\Component\Console\Event\ConsoleExceptionEvent')) {
                $event = new ConsoleExceptionEvent($command, $input, $output, $error, 1);
            }
        }

        if (empty($event)) {
            $this->markTestSkipped('No event defined.');
        }

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');

        $key = '';
        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            $key = class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')
                ? ConsoleEvents::ERROR
                : ConsoleEvents::EXCEPTION;
        } else {
            $this->markTestSkipped('Nothing to test.');
        }

        foreach ($eventDispatcher->getListeners('kernel.exception') as $listener) {
            $eventDispatcher->removeListener('kernel.exception', $listener);
        }

        $handler = new ErrorHandler();
        $handler->setAssert(function ($record) {
            $this->assertNotEmpty($record);

            $this->assertNotEmpty($record['context']['exception']);
            $exception = $record['context']['exception'];

            $this->assertInstanceOf(\Exception::class, $exception);

            $this->assertEquals('This is console exception', $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);
        });

        foreach ($eventDispatcher->getListeners($key) as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener || $listener[0] instanceof ErrorListener) {
                // disable default symfony listeners and current error listener
                $eventDispatcher->removeListener($key, $listener);
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        $eventDispatcher->dispatch($key, $event);
        set_error_handler($erHandler);
        static::ensureKernelShutdown();
    }
}
