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
        $handler  = new \SymfonyRollbarBundle\Provider\RollbarHandler($container);

        $hContainer = $handler->getContainer();
        $this->assertEquals($container, $hContainer);
        $this->assertInstanceOf(\Monolog\Handler\AbstractProcessingHandler::class, $handler);
    }

    /**
     * @dataProvider recordGenerator
     * @param $record
     */
    public function testWrite($record)
    {
        $this->markTestIncomplete('TODO: write body');
    }

    public function recordGenerator()
    {
        return [
            [
                [
                    'context' => [
                        'level' => \Monolog\Logger::ERROR,
                        'exception' => new \Exception('RecordGenerator :: #1'),
                        'message' => 'RecordGenerator :: #1',
                    ]
                ]
            ],
            [
                [
                    'context' => [
                        'level' => \Monolog\Logger::ERROR,
                        'message' => 'RecordGenerator :: #2',
                    ]
                ]
            ],
        ];
    }
}
