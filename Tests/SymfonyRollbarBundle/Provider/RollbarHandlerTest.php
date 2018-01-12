<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Rollbar\Payload\Level;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class RollbarHandlerTest
 *
 * @package SymfonyRollbarBundle\Tests\Provider
 */
class RollbarHandlerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testRollbarHandler()
    {
        $container = static::$kernel->getContainer();
        $handler   = new \SymfonyRollbarBundle\Provider\RollbarHandler($container);

        $hContainer = $handler->getContainer();
        $this->assertEquals($container, $hContainer);
        $this->assertInstanceOf(\Monolog\Handler\AbstractProcessingHandler::class, $handler);
    }

    /**
     * @dataProvider recordGenerator
     *
     * @param $record
     */
    public function testWrite($record)
    {
        $container = static::$kernel->getContainer();
        $handler   = new \SymfonyRollbarBundle\Provider\RollbarHandler($container);

        $property = new \ReflectionProperty($handler, 'hasRecords');
        $property->setAccessible(true);

        $method = new \ReflectionMethod($handler, 'write');
        $method->setAccessible(true);

        $this->assertFalse($property->getValue($handler));
        $method->invoke($handler, $record);
        $this->assertTrue($property->getValue($handler));
    }

    /**
     * @return array
     */
    public function recordGenerator()
    {
        return [
            [
                [
                    'message'    => 'RecordGenerator :: #1',
                    'datetime'   => new \DateTime(),
                    'level'      => \Monolog\Logger::ERROR,
                    'level_name' => \Monolog\Logger::ERROR,
                    'channel'    => 'symfony.rollbar',
                    'extra'      => [],
                    'context'    => [
                        'exception' => new \Exception('RecordGenerator :: #1'),
                    ],
                ],
            ],
            [
                [
                    'message'    => 'RecordGenerator :: #2',
                    'datetime'   => new \DateTime(),
                    'level'      => \Monolog\Logger::ERROR,
                    'level_name' => \Monolog\Logger::ERROR,
                    'channel'    => 'symfony.rollbar',
                    'extra'      => [],
                    'context'    => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider recordGenerator
     * @param $record
     */
    public function testClose($record)
    {
        $container = static::$kernel->getContainer();
        $handler   = new \SymfonyRollbarBundle\Provider\RollbarHandler($container);

        $property = new \ReflectionProperty($handler, 'hasRecords');
        $property->setAccessible(true);

        $method = new \ReflectionMethod($handler, 'write');
        $method->setAccessible(true);

        $this->assertFalse($property->getValue($handler));
        $method->invoke($handler, $record);
        $this->assertTrue($property->getValue($handler));

        $handler->close();
        $this->assertFalse($property->getValue($handler));
    }
}
