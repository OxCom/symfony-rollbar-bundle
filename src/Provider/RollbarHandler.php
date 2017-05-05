<?php
namespace SymfonyRollbarBundle\Provider;

use Rollbar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

class RollbarHandler
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
     * @return \Monolog\Handler\RollbarHandler
     */
    public function getHandler()
    {
        $config = $this->getContainer()->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

        Rollbar::init($config['rollbar'], false, false, false);
        $handler = new \Monolog\Handler\RollbarHandler(Rollbar::$instance);

        return $handler;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
