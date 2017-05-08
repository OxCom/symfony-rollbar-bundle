<?php
namespace Tests\SymfonyRollbarBundle\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RollbarHandlerTest
 * @package Tests\SymfonyRollbarBundle\Provider
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
        $provider  = new \SymfonyRollbarBundle\Provider\RollbarHandler($container);

        $cntr = $provider->getContainer();
        $this->assertEquals($container, $cntr);

        $handler = $provider->getHandler();
        $this->assertInstanceOf(\Monolog\Handler\RollbarHandler::class, $handler);
    }
}
