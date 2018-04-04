<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use SymfonyRollbarBundle\Tests\Fixtures\ApiClientMock;
use SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeException;

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
        $handler   = new RollbarHandler($container);

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
        $handler   = new RollbarHandler($container);

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
            [
                [
                    'message'    => 'RecordGenerator :: #3',
                    'datetime'   => new \DateTime(),
                    'level'      => \Monolog\Logger::ERROR,
                    'level_name' => \Monolog\Logger::ERROR,
                    'channel'    => 'symfony.rollbar',
                    'extra'      => [],
                    'context'    => [
                        'payload' => [1, 2, 3]
                    ],
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
        $handler   = new RollbarHandler($container);

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

    /**
     * @dataProvider generatorShouldSkip
     *
     * @param $e
     * @param $skip
     */
    public function testShouldSkip($e, $skip)
    {
        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $this->assertEquals($skip, $handler->shouldSkip($e));
    }

    /**
     * @return array
     */
    public function generatorShouldSkip()
    {
        $e = new \ErrorException();

        return [
            [$e, false],
            [new \Exception(), false],
            [new \SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeException(), true],
            [new \Symfony\Component\Debug\Exception\UndefinedFunctionException("error", $e), true],
            [new \TypeError("TypeError"), false],
            [new \ParseError("ParseError"), true],
        ];
    }

    public function testContextPayload()
    {
        $record = [
            'message'    => 'RecordGenerator :: #4',
            'datetime'   => new \DateTime(),
            'level'      => \Monolog\Logger::ERROR,
            'level_name' => \Monolog\Logger::ERROR,
            'channel'    => 'symfony.rollbar',
            'extra'      => [],
            'context'    => [
                'exception' => new MyAwesomeException('RecordGenerator :: #4'),
                'payload' => [4, 5, 6]
            ],
        ];

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $property = new \ReflectionProperty($handler, 'hasRecords');
        $property->setAccessible(true);

        $method = new \ReflectionMethod($handler, 'write');
        $method->setAccessible(true);

        $this->assertFalse($property->getValue($handler));
        $method->invoke($handler, $record);
        $this->assertFalse($property->getValue($handler));
    }

    /**
     * @dataProvider generatorTrackBuildData
     *
     * @param string $env
     * @param string $revision
     * @param string $comment
     * @param string $rollbarUser
     * @param string $localUser
     */
    public function testTrackBuildPayload($env, $revision, $comment, $rollbarUser, $localUser)
    {
        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        /** @var \SymfonyRollbarBundle\Tests\Fixtures\ApiClientMock $client */
        $client = new ApiClientMock($container);
        $container->set('symfony_rollbar.provider.api_client', $client);

        // here result is payload
        $payload = $handler->trackBuild($env, $revision, $comment, $rollbarUser, $localUser);

        $this->assertEquals('SOME_ROLLBAR_ACCESS_TOKEN_123456', $payload['access_token']);
        $this->assertEquals($env, $payload['environment']);
        $this->assertEquals($revision, $payload['revision']);
        $this->assertEquals($comment, $payload['comment']);
        $this->assertEquals($rollbarUser, $payload['rollbar_username']);
        $this->assertEquals($localUser, $payload['local_username']);
    }

    /**
     * @return array
     */
    public function generatorTrackBuildData()
    {
        return [
            ['test', 'R1.0.0', 'Hello', 'Rollbar', get_current_user()],
            ['test', 'R1.0.0', 'Hello', '', get_current_user()],
            ['test', 'R1.0.0', 'Hello', '', ''],
            ['test', 'R1.0.0', '', '', ''],
            ['test', 'R1.0.0', null, null, null],
        ];
    }

    /**
     * @covers \SymfonyRollbarBundle\Provider\ApiClient::trackBuild()
     */
    public function testTrackBuild()
    {
        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $handler->trackBuild('test', 'R1.0.0', 'Hello', 'Rollbar', get_current_user());
    }
}
