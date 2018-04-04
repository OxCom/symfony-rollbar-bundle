<?php

namespace SymfonyRollbarBundle\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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
            $this->assertInstanceOf(\Twig_Function::class, $function);

            $output = $rollbarTwig->rollbarJs();
            $this->assertContains('_rollbarConfig', $output);
            $this->assertContains('SOME_ROLLBAR_ACCESS_TOKEN_654321', $output);
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
//            ['test_drb', true],
        ];
    }
}
