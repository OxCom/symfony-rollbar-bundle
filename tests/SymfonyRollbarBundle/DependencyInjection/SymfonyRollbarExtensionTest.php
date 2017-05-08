<?php
namespace Tests\SymfonyRollbarBundle\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

/**
 * Class SymfonyRollbarExtensionTest
 * @package Tests\SymfonyRollbarBundle\DependencyInjection
 */
class SymfonyRollbarExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @link: https://github.com/matthiasnoback/SymfonyDependencyInjectionTest
     * @return array
     */
    protected function getContainerExtensions()
    {
        return array(
            new SymfonyRollbarExtension()
        );
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @param string $var
     * @param mixed $value
     */
    public function testConfigEnabledVars($var, $value)
    {
        $this->load();

        $this->assertContainerBuilderHasParameter($var, $value);
    }

    public function generatorConfigVars()
    {
        return [
            ['symfony_rollbar.event_listener.exception_listener.class', \SymfonyRollbarBundle\EventListener\ExceptionListener::class],
            ['symfony_rollbar.event_listener.error_listener.class', \SymfonyRollbarBundle\EventListener\ErrorListener::class],
            ['symfony_rollbar.provider.rollbar_handler.class', \SymfonyRollbarBundle\Provider\RollbarHandler::class],
            ['symfony_rollbar.payload.generator.class', \SymfonyRollbarBundle\Payload\Generator::class],
            ['symfony_rollbar.config', ['enable' => true]],
        ];
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     *
     * @param string $var
     * @param mixed $value
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
