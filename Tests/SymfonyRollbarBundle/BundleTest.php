<?php
namespace Tests\SymfonyRollbarBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BundleTest
 * @package Tests\SymfonyRollbarBundle
 */
class BundleTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testBoot()
    {
        $b = static::$kernel->getBundles();
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners       = $eventDispatcher->getListeners();
        $exception       = new \Exception('This is new report');
        $event           = new GetResponseForExceptionEvent(
            static::$kernel, new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $eventDispatcher->dispatch('kernel.exception', $event);
    }
}
