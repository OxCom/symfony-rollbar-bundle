<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Provider\AbstractPersonProvider;
use SymfonyRollbarBundle\Provider\RollbarHandler;
use SymfonyRollbarBundle\Tests\Fixtures\AwesomePerson;

/**
 * Class PersonProviderTest
 *
 * @package SymfonyRollbarBundle\Tests\Provider
 */
class PersonProviderTest extends KernelTestCase
{
    /**
     * @dataProvider generatorProviderEnv
     *
     * @param string $env
     * @param \SymfonyRollbarBundle\Provider\PersonInterface $expected
     *
     * @throws \ReflectionException
     */
    public function testPersonProvider($env, $expected)
    {
        static::bootKernel(['environment' => $env]);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $method = new \ReflectionMethod($handler, 'initialize');
        $method->setAccessible(true);

        $config = $method->invoke($handler);
        $this->assertNotEmpty($config['person_fn']);

        $call = $config['person_fn'];
        $this->assertEquals(2, count($call), "The 'person_fn' should contains 2 elements");

        /** @var AbstractPersonProvider $service */
        $service = $call[0];
        $method  = $call[1];
        $this->assertInstanceOf(AbstractPersonProvider::class, $service);
        $this->assertEquals('getPerson', $method);

        $service->setPerson($expected);
        $person = call_user_func($call);

        if (!empty($person)) {
            $this->assertEquals($expected->getId(), $person['id']);
            $this->assertEquals($expected->getUsername(), $person['username']);
            $this->assertEquals($expected->getEmail(), $person['email']);
        } else {
            $this->assertNull($person);
        }
    }

    public function generatorProviderEnv()
    {
        return [
            ['test', null],
            ['test', new AwesomePerson('person_id_1', 'username', 'email')],
            ['test_is', null],
            ['test_is', new AwesomePerson('person_id_2', 'username', 'email')],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testPersonProviderFunction()
    {
        include_once __DIR__ . '/../../Fixtures/global_fn.php';
        static::bootKernel(['environment' => 'test_if']);

        $container = static::$kernel->getContainer();
        $handler   = new RollbarHandler($container);

        $method = new \ReflectionMethod($handler, 'initialize');
        $method->setAccessible(true);

        $config = $method->invoke($handler);
        $this->assertNotEmpty($config['person_fn']);

        $method = $config['person_fn'];
        $this->assertEquals('get_awesome_person', $method);

        $person = call_user_func($method);

        $this->assertEquals('global_id', $person['id']);
        $this->assertEquals('global_username', $person['username']);
        $this->assertEquals('global_email', $person['email']);
    }
}
