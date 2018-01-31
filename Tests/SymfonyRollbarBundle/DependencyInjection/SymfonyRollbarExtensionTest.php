<?php
namespace SymfonyRollbarBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use SymfonyRollbarBundle\EventListener\ErrorListener;
use SymfonyRollbarBundle\EventListener\ExceptionListener;
use SymfonyRollbarBundle\Payload\Generator;
use SymfonyRollbarBundle\Provider\RollbarHandler;

/**
 * Class SymfonyRollbarExtensionTest
 * @package SymfonyRollbarBundle\Tests\DependencyInjection
 */
class SymfonyRollbarExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @link: https://github.com/matthiasnoback/SymfonyDependencyInjectionTest
     * @return array
     */
    protected function getContainerExtensions()
    {
        return [
            new SymfonyRollbarExtension(),
        ];
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @param string $var
     * @param mixed  $value
     */
    public function testConfigEnabledVars($var, $value)
    {
        $this->load();

        $this->assertContainerBuilderHasParameter($var, $value);
    }

    public function generatorConfigVars()
    {
        $exclude = Configuration::$exclude;

        return [
            ['symfony_rollbar.event_listener.exception_listener.class', ExceptionListener::class],
            ['symfony_rollbar.event_listener.error_listener.class', ErrorListener::class],
            ['symfony_rollbar.provider.rollbar_handler.class', RollbarHandler::class],
            ['symfony_rollbar.config', ['enable' => true, 'exclude' => $exclude]],
        ];
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     *
     * @param string $var
     * @param mixed  $value
     */
    public function testConfigDisabledVars($var, $value)
    {
        $this->load(['enable' => false]);

        $this->assertContainerBuilderHasParameter($var, $value);
    }

    public function testAlias()
    {
        $extension = new SymfonyRollbarExtension();
        $this->assertEquals(SymfonyRollbarExtension::ALIAS, $extension->getAlias());
    }
}
