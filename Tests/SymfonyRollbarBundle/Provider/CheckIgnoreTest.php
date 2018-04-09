<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use SymfonyRollbarBundle\Tests\Fixtures\CheckIgnoreProvider;

/**
 * Class CheckIgnoreTest
 *
 * @package SymfonyRollbarBundle\Tests\Provider
 */
class CheckIgnoreTest extends KernelTestCase
{
    /**
     * @dataProvider generatorProviderEnv
     *
     * @param string $env
     * @param \SymfonyRollbarBundle\Provider\PersonInterface $expected
     *
     * @throws \ReflectionException
     */
    public function testCheckIgnore($env, $expected)
    {
        static::bootKernel(['environment' => $env]);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $method = new \ReflectionMethod($handler, 'initialize');
        $method->setAccessible(true);

        $config = $method->invoke($handler);
        $this->assertNotEmpty($config['checkIgnore']);

        $call = $config['checkIgnore'];
        $this->assertEquals(2, count($call), "The 'checkIgnore' should contains 2 elements");

        /** @var \SymfonyRollbarBundle\Tests\Fixtures\CheckIgnoreProvider $service */
        $service = $call[0];
        $method  = $call[1];
        $this->assertInstanceOf(CheckIgnoreProvider::class, $service);
        $this->assertEquals('checkIgnore', $method);

        $service->setIgnore($expected);

        $message = new \Rollbar\Payload\Message('rollbar');
        $body    = new \Rollbar\Payload\Body($message);
        $data    = new \Rollbar\Payload\Data($env, $body);
        $payload = new \Rollbar\Payload\Payload($data, $config['access_token']);

        $ignore = call_user_func($call, false, 'toLog', $payload);

        if (!empty($ignore)) {
            $this->assertEquals($expected, $ignore);
        } else {
            $this->assertFalse($ignore);
        }
    }

    public function generatorProviderEnv()
    {
        return [
            ['test', false],
            ['test', true],
            ['test_is', false],
            ['test_is', true],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testPersonProviderFunction()
    {
        $env = 'test_if';
        include_once __DIR__ . '/../../Fixtures/global_fn.php';
        static::bootKernel(['environment' => $env]);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $method = new \ReflectionMethod($handler, 'initialize');
        $method->setAccessible(true);

        $config = $method->invoke($handler);
        $this->assertNotEmpty($config['checkIgnore']);

        $method = $config['checkIgnore'];
        $this->assertEquals('should_ignore', $method);

        $message = new \Rollbar\Payload\Message('rollbar');
        $body    = new \Rollbar\Payload\Body($message);
        $data    = new \Rollbar\Payload\Data($env, $body);
        $payload = new \Rollbar\Payload\Payload($data, $config['access_token']);

        $ignore = call_user_func($method, false, 'toLog', $payload);
        $this->assertFalse($ignore);
    }
}
