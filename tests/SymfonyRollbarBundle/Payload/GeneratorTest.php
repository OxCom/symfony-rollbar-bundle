<?php
namespace Tests\SymfonyRollbarBundle\Payload;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use SymfonyRollbarBundle\Payload\ErrorItem;
use SymfonyRollbarBundle\Payload\Generator;
use SymfonyRollbarBundle\Payload\TraceChain;

/**
 * Class GeneratorTest
 * @package Tests\SymfonyRollbarBundle\Payload
 */
class GeneratorTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testGetContainer()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        $ctnr = $generator->getContainer();

        $this->assertEquals($container, $ctnr);
    }

    public function testGetKernel()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        $kernel = $generator->getKernel();

        $this->assertEquals(static::$kernel, $kernel);
    }

    /**
     * @param $class
     * @param $method
     *
     * @return \ReflectionMethod
     */
    protected static function getClassMethod($class, $method)
    {
        $class  = new \ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }


    public function testGetServerInfo()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        $method = static::getClassMethod(Generator::class, 'getServerInfo');
        $data   = $method->invoke($generator);

        $this->assertArrayHasKey('host', $data);
        $this->assertArrayHasKey('root', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('file', $data);
        $this->assertArrayHasKey('argv', $data);

        $this->assertEquals($data['host'], gethostname());
        $this->assertEquals($data['root'], static::$kernel->getRootDir());
        $this->assertEquals($data['user'], get_current_user());
    }

    public function testGetRequestInfo()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        /**
         * @var \Symfony\Component\HttpFoundation\Request $request
         */
        $request = $container->get('request_stack')->getCurrentRequest();
        if (empty($request)) {
            $request = new Request();
        }

        $method = static::getClassMethod(Generator::class, 'getRequestInfo');
        $data   = $method->invoke($generator);

        $this->assertArrayHasKey('url', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('query_string', $data);
        $this->assertArrayHasKey('body', $data);
        $this->assertArrayHasKey('user_ip', $data);

        $this->assertEquals($data['url'], $request->getRequestUri());
        $this->assertEquals($data['method'], $request->getMethod());
        $this->assertEquals($data['headers'], $request->headers->all());
        $this->assertEquals($data['query_string'], $request->getQueryString());
        $this->assertEquals($data['body'], $request->getContent());
        $this->assertEquals($data['user_ip'], $request->getClientIp());
    }

    public function testGetErrorPayload()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        $serverMethod = static::getClassMethod(Generator::class, 'getServerInfo');
        $serverInfo   = $serverMethod->invoke($generator);

        $requestMethod = static::getClassMethod(Generator::class, 'getRequestInfo');
        $requestInfo   = $requestMethod->invoke($generator);

        $item = new ErrorItem();
        $code = E_ERROR;
        $msg  = 'testGetErrorPayload';
        $file = __FILE__;
        $line = rand(1, 10);

        list($message, $payload) = $generator->getErrorPayload($code, $msg, $file, $line);

        $this->assertEquals($msg, $message);

        $this->assertArrayHasKey('body', $payload);
        $this->assertArrayHasKey('request', $payload);
        $this->assertArrayHasKey('environment', $payload);
        $this->assertArrayHasKey('framework', $payload);
        $this->assertArrayHasKey('language_version', $payload);
        $this->assertArrayHasKey('server', $payload);

        $body = ['trace' => $item($code, $msg, $file, $line)];

        $this->assertEquals($body, $payload['body']);
        $this->assertEquals($requestInfo, $payload['request']);
        $this->assertEquals(static::$kernel->getEnvironment(), $payload['environment']);
        $this->assertEquals(\Symfony\Component\HttpKernel\Kernel::VERSION, $payload['framework']);
        $this->assertEquals(phpversion(), $payload['language_version']);
        $this->assertEquals($serverInfo, $payload['server']);
    }

    public function testGetExceptionPayload()
    {
        /**
         * @var \SymfonyRollbarBundle\Payload\Generator $generator
         */
        $container = static::$kernel->getContainer();
        $generator = $container->get('symfony_rollbar.payload.generator');

        $serverMethod = static::getClassMethod(Generator::class, 'getServerInfo');
        $serverInfo   = $serverMethod->invoke($generator);

        $requestMethod = static::getClassMethod(Generator::class, 'getRequestInfo');
        $requestInfo   = $requestMethod->invoke($generator);

        $msg       = 'getExceptionPayload';
        $code      = E_ERROR;
        $exception = new \Exception($msg, $code);
        $chain     = new TraceChain();

        list($message, $payload) = $generator->getExceptionPayload($exception);

        $this->assertContains($msg, $message);

        $this->assertArrayHasKey('body', $payload);
        $this->assertArrayHasKey('request', $payload);
        $this->assertArrayHasKey('environment', $payload);
        $this->assertArrayHasKey('framework', $payload);
        $this->assertArrayHasKey('language_version', $payload);
        $this->assertArrayHasKey('server', $payload);

        $body = ['trace_chain' => $chain($exception)];

        $this->assertEquals($body, $payload['body']);
        $this->assertEquals($requestInfo, $payload['request']);
        $this->assertEquals(static::$kernel->getEnvironment(), $payload['environment']);
        $this->assertEquals(\Symfony\Component\HttpKernel\Kernel::VERSION, $payload['framework']);
        $this->assertEquals(phpversion(), $payload['language_version']);
        $this->assertEquals($serverInfo, $payload['server']);
    }
}
