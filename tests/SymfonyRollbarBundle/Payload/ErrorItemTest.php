<?php
namespace Tests\SymfonyRollbarBundle\Payload;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Payload\ErrorItem;

/**
 * Class ErrorItemTest
 * @package Tests\SymfonyRollbarBundle\Payload
 */
class ErrorItemTest extends KernelTestCase
{
    /**
     * @dataProvider generateInvoke
     *
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string $mapped
     */
    public function testInvoke($code, $message, $file, $line, $mapped)
    {
        $item = new ErrorItem();
        $data = $item($code, $message, $file, $line);

        $this->assertNotEmpty($data['exception']);
        $this->assertNotEmpty($data['frames']);

        $exception = $data['exception'];
        $this->assertEquals($mapped, $exception['class']);
        $this->assertContains($message, $exception['message']);

        $this->assertEquals(1, count($data['frames']));

        $frame = $data['frames'][0];
        $this->assertEquals($file, $frame['filename']);
        $this->assertEquals($line, $frame['lineno']);
    }

    /**
     * @return array
     */
    public function generateInvoke()
    {
        return [
            [E_ERROR, 'Error message - ' . microtime(true), __FILE__, rand(1, 100), 'E_ERROR'],
            [E_WARNING, 'Error message - ' . microtime(true), __FILE__, rand(1, 100), 'E_WARNING'],
            [E_PARSE, 'Error message - ' . microtime(true), __FILE__, rand(1, 100), 'E_PARSE'],
            [E_NOTICE, 'Error message - ' . microtime(true), __FILE__, rand(1, 100), 'E_NOTICE'],
            [E_CORE_ERROR, 'Error message - ' . microtime(true), __FILE__, rand(1, 100), 'E_CORE_ERROR'],
        ];
    }
}
