<?php

namespace SymfonyRollbarBundle\Tests\SymfonyRollbarBundle\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Provider\RollbarHandler;

/**
 * Class CustomDataMethodTest
 *
 * @package SymfonyRollbarBundle\Tests\SymfonyRollbarBundle\Provider
 */
class CustomDataMethodTest extends KernelTestCase
{
    /**
     * @dataProvider generatorProviderEnv
     *
     * @param string $env
     *
     * @throws \ReflectionException
     */
    public function testCustomDataMethod($env)
    {
        include_once __DIR__ . '/../../Fixtures/global_fn.php';
        static::bootKernel(['environment' => $env]);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $method = new \ReflectionMethod($handler, 'initialize');
        $method->setAccessible(true);

        $config = $method->invoke($handler);
        $this->assertNotEmpty($config['custom_data_method']);

        $call = $config['custom_data_method'];
        $this->assertTrue(is_callable($call), 'it should be possible to call custom data method.');

        $toLog   = 'some-strange-value';
        $context = 'this-is-context';
        $result  = $call($toLog, $context);
        $this->assertEquals($toLog, $result['to_log']);
        $this->assertEquals($context, $result['context']);
        $this->assertGreaterThan(0, $result['random_number']);
    }

    /**
     * @return array
     */
    public function generatorProviderEnv()
    {
        return [
            ['test_if'],
            ['test_is'],
        ];
    }
}
