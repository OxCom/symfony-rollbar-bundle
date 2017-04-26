<?php
namespace Tests\SymfonyRollbarBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ErrorListenerTest
 * @package Tests\SymfonyRollbarBundle\EventListener
 */
class ErrorListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    /**
     * There should not be any error during tests
     */
    public function testUserError()
    {
        trigger_error("Fatal error", E_USER_ERROR);
    }
}
