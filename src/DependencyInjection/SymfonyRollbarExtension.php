<?php

namespace SymfonyRollbarBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class Extension
 * @package SymfonyRollbarBundle\DependencyInjection
 */
class SymfonyRollbarExtension extends Extension
{
    const ALIAS = 'symfony_rollbar';

    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        // load services
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter(static::ALIAS . '.config', $config);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return static::ALIAS;
    }
}
