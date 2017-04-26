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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get payload a log record.
     *
     * @param \Exception                                $exception
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getPayload(\Exception $exception, Request $request)
    {
        // handle exception
        $chain  = new TraceChain();
        $item   = new TraceItem();
        $kernel = $this->getContainer()->get('kernel');

        $data    = $item($exception);
        $message = $data['exception']['message'];
        $args    = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];

        /**
         * Build payload
         * @link https://rollbar.com/docs/api/items_post/
         */
        $payload = [
            'body'             => ['trace_chain' => $chain($exception)],
            'request'          => [
                'url'          => $request->getRequestUri(),
                'method'       => $request->getMethod(),
                'headers'      => $request->headers->all(),
                'query_string' => $request->getQueryString(),
                'body'         => $request->getContent(),
                'user_ip'      => $request->getClientIp(),
            ],
            'environment'      => $kernel->getEnvironment(),
            'framework'        => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'language_version' => phpversion(),
            'server' => [
                'host' => gethostname(),
                'root' => $kernel->getRootDir(),
                'user' => get_current_user(),
                'file' => array_shift($args),
                'argv' => implode(' ', $args),
            ],
        ];

        return [$message, $payload];
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
