<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ApiClientMock
 *
 * @package SymfonyRollbarBundle\Tests\Fixtures
 */
class ApiClientMock extends \SymfonyRollbarBundle\Provider\ApiClient
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * ApiClientMock constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->setCallback();
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    public function trackBuild($payload = [])
    {
        $cb = $this->callback;

        return $cb($payload);
    }

    /**
     * This callback will be fired instead of API call
     *
     * @param callable $cb
     *
     * @return $this
     */
    public function setCallback($cb = null)
    {
        if (!empty($cb) && is_callable($cb)) {
            $this->callback = $cb;
        } else {
            $this->callback = function($payload) {
                return $payload;
            };
        }

        return $this;
    }
}
