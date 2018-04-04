<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\Provider\AbstractPersonProvider;

class PersonProvider extends AbstractPersonProvider
{
    /**
     * Initialize current person that should be tracked with Rollbar
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
    }
}
