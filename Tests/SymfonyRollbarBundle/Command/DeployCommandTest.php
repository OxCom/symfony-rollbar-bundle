<?php

namespace SymfonyRollbarBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use SymfonyRollbarBundle\Command\DeployCommand;
use SymfonyRollbarBundle\Tests\Fixtures\ApiClientMock;

/**
 * Class DeployCommandTest
 */
class DeployCommandTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();
    }

    public function testRegistration()
    {
        $application = new Application(static::$kernel);
        $application->add(new DeployCommand());

        try {
            $application->find('rollbar:deploy');
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }
    }

    /**
     * @dataProvider generateExecuteInput
     *
     * @param array $input
     * @param       $expected
     * @param       $message
     */
    public function testExecute($input, $expected, $message)
    {
        $container = static::$kernel->getContainer();

        /** @var \SymfonyRollbarBundle\Tests\Fixtures\ApiClientMock $client */
        $client = new ApiClientMock($container);
        $client->setCallback(function ($payload) use ($input) {
            $this->assertEquals('SOME_ROLLBAR_ACCESS_TOKEN_123456', $payload['access_token']);
            $this->assertNotEmpty($payload['environment']);
            $this->assertEquals($input['revision'], $payload['revision']);

            if (!empty($input['-c'])) {
                $this->assertEquals($input['-c'], $payload['comment']);
            }

            if (!empty($input['-ru'])) {
                $this->assertEquals($input['-ru'], $payload['rollbar_username']);
            }

            if (!empty($input['-lu'])) {
                $this->assertEquals($input['-lu'], $payload['local_username']);
            }

            return true;
        });
        $container->set('symfony_rollbar.provider.api_client', $client);

        $application = new Application(static::$kernel);
        $application->add(new DeployCommand());

        try {
            $command = $application->find('rollbar:deploy');
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $commandTester = new CommandTester($command);

        if ($expected === 'exception-args') {
            $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
            $this->expectExceptionMessageRegExp($message);
        }

        $commandTester->execute($input);

        if ($expected === 'general') {
            $output = $commandTester->getDisplay();
            $this->assertContains($message, $output);
        }
    }

    /**
     * @return array
     */
    public function generateExecuteInput()
    {
        return [
            [[], 'exception-args', '/revision/'],
            [['revision' => 'R1.0.0'], 'general', 'Done.'],
            [['revision' => 'R1.0.0', '-c' => 'World'], 'general', 'Done.'],
            [['revision' => 'R1.0.0', '-ru' => 'Rollbar'], 'general', 'Done.'],
            [['revision' => 'R1.0.0', '-lu' => get_current_user()], 'general', 'Done.'],
        ];
    }

    public function testExecuteGeneric()
    {
        $application = new Application(static::$kernel);
        $application->add(new DeployCommand());

        $command       = $application->find('rollbar:deploy');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'revision' => 'R1.0.0',
            '-c'       => 'World',
            '-lu'      => get_current_user(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Build has been not tracked:', $output);
    }
}
