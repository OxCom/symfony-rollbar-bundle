<?php

namespace SymfonyRollbarBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

/**
 * Class ApiClient
 *
 * @package SymfonyRollbarBundle\Provider
 */
class ApiClient
{
    const API_VERSION = '1';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * ApiClient constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $config         = $this->container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $this->endpoint = $config['rollbar']['base_api_url'];

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint,
        ]);
    }

    /**
     * @TODO : inject mocked clienty
     *
     * @param array $payload
     *
     * @link https://rollbar.com/docs/api/deploys/
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function trackBuild($payload = [])
    {
        $rsp = $this->client->post('deploy', [
            'form_params' => $payload,
        ]);

        return $rsp;
    }
}
