<?php

namespace SymfonyRollbarBundle\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\Twig\RollbarExtension;

/**
 * Class RollbarExtensionTest
 *
 * @package SymfonyRollbarBundle\Tests\Provider
 */
class RollbarExtensionTest extends KernelTestCase
{
    /**
     * @dataProvider generatorRollbarEnv
     *
     * @param $env
     * @param $isDisabled
     *
     * @throws \ReflectionException
     */
    public function testRollbarJs($env, $isDisabled)
    {
        static::bootKernel(['environment' => $env]);

        $container   = static::$kernel->getContainer();
        $rollbarTwig = new RollbarExtension($container);

        $property = new \ReflectionProperty($rollbarTwig, 'config');
        $property->setAccessible(true);

        $config = $property->getValue($rollbarTwig);
        $list   = $rollbarTwig->getFunctions();

        if ($isDisabled) {
            $this->assertNull($config);
            $this->assertEmpty($list);
        } else {
            $this->assertNotNull($config);
            $this->assertNotEmpty($list);

            $function = $list[0];
            $this->assertInstanceOf(\Twig\TwigFunction::class, $function);

            $output = $rollbarTwig->rollbarJs();

            $this->assertNotContains('var _rollbarConfig = var _rollbarConfig', $output);
            $this->assertContains('_rollbarConfig', $output);
            $this->assertContains('SOME_ROLLBAR_ACCESS_TOKEN_654321', $output);
            $this->assertContains('_rollbarConfig.rollbarJsUrl', $output);
        }
    }

    /**
     * @return array
     */
    public function generatorRollbarEnv()
    {
        return [
            // env, is-empty-functions
            ['test', false],
            ['test_drb', true],
            ['test_drbj', true],
        ];
    }

    public function testConfigMap()
    {
        static::bootKernel();

        $container   = static::$kernel->getContainer();
        $rollbarTwig = new RollbarExtension($container);

        $method = new \ReflectionMethod($rollbarTwig, 'getJsConfig');
        $method->setAccessible(true);

        $config = $method->invoke($rollbarTwig);
        $expected = [
            'accessToken'                => 'SOME_ROLLBAR_ACCESS_TOKEN_654321',
            'payload'                    => ['environment' => static::$kernel->getEnvironment()],
            'enabled'                    => true,
            'captureUncaught'            => true,
            'uncaughtErrorLevel'         => Configuration::JS_UNCAUGHT_LEVEL,
            'captureUnhandledRejections' => true,
            'ignoredMessages'            => [],
            'verbose'                    => false,
            'async'                      => true,
            'autoInstrument'             => Configuration::$autoInstrument,
            'itemsPerMinute'             => Configuration::JS_ITEMS_PER_MINUTE,
            'maxItems'                   => Configuration::JS_MAX_ITEMS,
            'scrubFields'                => Configuration::$scrubFieldsDefault,
        ];

        $this->assertEquals($expected, $config);
    }
}
