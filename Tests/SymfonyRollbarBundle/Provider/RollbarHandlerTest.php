<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use SymfonyRollbarBundle\Tests\Fixtures\ApiClientMock;
use SymfonyRollbarBundle\Tests\Fixtures\CheckIgnoreProvider;
use SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeException;
use SymfonyRollbarBundle\Tests\Fixtures\PersonProvider;

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
     *
     * @throws \ReflectionException
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
     * @dataProvider recordGenerator
     *
     * @param $record
     *
     * @throws \ReflectionException
     */
    public function testWriteDisabledRollbar($record)
    {
        static::bootKernel(['environment' => 'test_drb']);

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
                        'payload' => [1, 2, 3],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider recordGenerator
     *
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
            [new \SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeSymfonyException(), true],
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
                'payload'   => [4, 5, 6],
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

    /**
     * @covers \SymfonyRollbarBundle\Provider\ApiClient::trackBuild()
     */
    public function testTrackBuildDisabledRollbar()
    {
        static::bootKernel(['environment' => 'test_drb']);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $result = $handler->trackBuild('test_drb', 'R1.0.0');
        $this->assertNull($result);

        /** @var \SymfonyRollbarBundle\Provider\ApiClient $client */
        $this->assertFalse($container->has('symfony_rollbar.provider.api_client'));
    }

    public function testInitialize()
    {
        $container = static::$kernel->getContainer();

        $mock = $this->createMock(RollbarHandler::class);

        $mock->method('getContainer')
            ->willReturn($container);

        $method = new \ReflectionMethod($mock, 'initialize');
        $method->setAccessible(true);
        $config = $method->invoke($mock);

        $defaultErrorMask = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

        $errorRates = [
            E_NOTICE      => 0.1,
            E_USER_ERROR  => 0.5,
            E_USER_NOTICE => 0.1,
        ];

        $exceptionRates = [
            '\Symfony\Component\Security\Core\Exception\AccessDeniedException'                      => 0.1,
            '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException'                         => 0.5,
            '\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException'                     => 0.5,
            '\Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException' => 1,
        ];

        $custom = [
            'hello' => 'world',
            'key'   => 'value',
        ];

        $root = \method_exists(static::$kernel, 'getProjectDir')
            ? static::$kernel->getProjectDir()
            : static::$kernel->getRootDir();

        $default = [
            'access_token'                   => 'SOME_ROLLBAR_ACCESS_TOKEN_123456',
            'agent_log_location'             => static::$kernel->getLogDir() . '/rollbar.log',
            'base_api_url'                   => Configuration::API_ENDPOINT,
            'branch'                         => Configuration::BRANCH,
            'autodetect_branch'              => false,
            'capture_error_stacktraces'      => true,
            'check_ignore'                   => [new CheckIgnoreProvider(), 'checkIgnore'],
            'code_version'                   => '',
            'environment'                    => static::$kernel->getEnvironment(),
            'error_sample_rates'             => $errorRates,
            'handler'                        => Configuration::HANDLER_BLOCKING,
            'include_error_code_context'     => false,
            'include_exception_code_context' => false,
            'included_errno'                 => $defaultErrorMask,
            'logger'                         => null,
            'person'                         => null,
            'person_fn'                      => [new PersonProvider($container), 'getPerson'],
            'root'                           => $root,
            'scrub_fields'                   => Configuration::$scrubFieldsDefault,
            'timeout'                        => 3,
            'report_suppressed'              => false,
            'use_error_reporting'            => false,
            'proxy'                          => null,
            'allow_exec'                     => true,
            'endpoint'                       => Configuration::API_ENDPOINT,
            'custom'                         => $custom,
            'exception_sample_rates'         => $exceptionRates,
            'fluent_host'                    => '127.0.0.1',
            'fluent_port'                    => 24224,
            'fluent_tag'                     => 'rollbar',
            'host'                           => null,
            'scrub_whitelist'                => null,
            'send_message_trace'             => false,
            'include_raw_request_body'       => false,
            'local_vars_dump'                => true,
            'capture_email'                  => false,
            'capture_ip'                     => true,
            'capture_username'               => false,
            'custom_data_method'             => null,
            'custom_truncation'              => null,
            'ca_cert_path'                   => null,
            'transformer'                    => null,
            'framework'                      => 'Symfony ' . \Symfony\Component\HttpKernel\Kernel::VERSION,
            'max_nesting_depth'              => -1,
            'max_items'                      => Configuration::PHP_MAX_ITEMS,
            'log_payload'                    => false,
            'raise_on_error'                 => false,
            'transmit'                       => false,
            'verbose'                        => Configuration::VERBOSE,
            'minimum_level'                  => Configuration::MIN_OCCURRENCES_LEVEL,
        ];

        $this->assertEquals($default, $config);
    }
}
