<?php
namespace Tests\SymfonyRollbarBundle\Payload;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Payload\TraceChain;

/**
 * Class TraceChainTest
 * @package Tests\SymfonyRollbarBundle\Payload
 */
class TraceChainTest extends KernelTestCase
{
    public function testInvoke()
    {
        $previous = new \Exception('Exception', 1);
        $previous = new \Exception('Exception', 2, $previous);
        $ex       = new \Exception('Exception', 3, $previous);

        $trace = new TraceChain();
        $chain = $trace($ex);

        $this->assertEquals(3, count($chain));

        foreach ($chain as $item) {
            $this->assertArrayHasKey('exception', $item);
            $this->assertArrayHasKey('frames', $item);
        }
    }
}
