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
use SymfonyRollbarBundle\Tests\Fixtures\ErrorHandler;

/**
 * Class ConsoleListenerTest
 * @package SymfonyRollbarBundle\Tests\SymfonyRollbarBundle\EventListener
 * @runTestsInSeparateProcesses
 */
class ConsoleListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();
    }

    /**
     * @dataProvider provideLegacyEvents
     *
     * @param $error
     * @param $event
     */
    public function testLegacyConsoleException($error, $event)
    {
        $container = static::$kernel->getContainer();

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
        $handler->setAssert(function ($record) use ($error) {
            $this->assertNotEmpty($record);

            $this->assertNotEmpty($record['context']['exception']);
            $exception = $record['context']['exception'];

            $this->assertEquals($error->getMessage(), $record['message']);
            $this->assertEquals(Logger::ERROR, $record['level']);

            $this->assertInstanceOf(\Exception::class, $exception);
        });

        foreach ($eventDispatcher->getListeners($key) as $listener) {
            /**
             * @var AbstractListener $listener
             */
            if (!$listener[0] instanceof AbstractListener) {
                // disable default symfony listeners
                $eventDispatcher->removeListener($key, $listener);
                continue;
            }

            $listener[0]->getLogger()->setHandlers([$handler]);
        }

        $eventDispatcher->dispatch($key, $event);
        restore_error_handler();
    }

    /**
     * @return array
     */
    public function provideLegacyEvents()
    {
        $input = new ArrayInput([]);
        $output = new StreamOutput(
            fopen('php://memory', 'w', false),
            OutputInterface::VERBOSITY_QUIET,
            false
        );

        $error = new \Exception('This is console exception');
        $command = new DeployCommand();

        $events = [];

        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            if (class_exists('Symfony\Component\Console\Event\ConsoleErrorEvent')) {
                $events[] = [$error, new ConsoleErrorEvent($input, $output, $error, $command)];
            }

            if (class_exists('\Symfony\Component\Console\Event\ConsoleExceptionEvent')) {
                $events[] = [$error, new ConsoleExceptionEvent($command, $input, $output, $error, 1)];
            }
        }

        return $events;
    }
}
