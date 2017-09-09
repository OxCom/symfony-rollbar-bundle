<?php

namespace SymfonyRollbarBundle\Payload;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Generator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $kernel;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->kernel    = $container->get('kernel');
    }

    /**
     * Get payload a log record.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    public function getExceptionPayload($exception)
    {
        /**
         * Build payload
         * @link https://rollbar.com/docs/api/items_post/
         */
        $payload = [
            'body'             => [],
            'framework'        => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'server'           => $this->getServerInfo(),
            'language_version' => phpversion(),
            'request'          => $this->getRequestInfo(),
            'environment'      => $this->getKernel()->getEnvironment(),
        ];

        // @link http://php.net/manual/en/reserved.constants.php
        // @link http://php.net/manual/en/language.errors.php7.php
        if (!($exception instanceof \Exception) || PHP_MAJOR_VERSION > 7 && !($exception instanceof \Throwable)) {
            $payload['body'] = $this->buildGeneratorError($exception, __FILE__, __LINE__);

            return ['Undefined error', $payload];
        }

        // handle exception
        $chain = new TraceChain();
        $item  = new TraceItem();

        $data            = $item($exception);
        $message         = $data['exception']['message'];
        $payload['body'] = ['trace_chain' => $chain($exception)];

        return [$message, $payload];
    }

    /**
     * @param $object
     * @param $file
     * @param $line
     *
     * @return array
     */
    protected function buildGeneratorError($object, $file, $line)
    {
        $item = new ErrorItem();

        return ['trace' => $item(0, serialize($object), $file, $line)];
    }

    /**
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return array
     */
    public function getErrorPayload($code, $message, $file, $line)
    {
        /**
         * @var \Symfony\Component\HttpFoundation\Request $request
         */
        $item = new ErrorItem();

        $payload = [
            'body'             => ['trace' => $item($code, $message, $file, $line)],
            'request'          => $this->getRequestInfo(),
            'environment'      => $this->getKernel()->getEnvironment(),
            'framework'        => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'language_version' => phpversion(),
            'server'           => $this->getServerInfo(),
        ];

        return [$message, $payload];
    }

    /**
     * @return array
     */
    protected function getRequestInfo()
    {
        /**
         * @var \Symfony\Component\HttpFoundation\Request $request
         */
        $request = $this->getContainer()->get('request_stack')->getCurrentRequest();
        if (empty($request)) {
            $request = new Request();
        }

        return [
            'url'          => $request->getRequestUri(),
            'method'       => $request->getMethod(),
            'headers'      => $request->headers->all(),
            'query_string' => $request->getQueryString(),
            'body'         => $request->getContent(),
            'user_ip'      => $request->getClientIp(),
        ];
    }

    /**
     * @return array
     */
    protected function getServerInfo()
    {
        $args   = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
        $kernel = $this->getKernel();

        return [
            'host' => gethostname(),
            'root' => $kernel->getRootDir(),
            'user' => get_current_user(),
            'file' => array_shift($args),
            'argv' => implode(' ', $args),
        ];
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }
}
