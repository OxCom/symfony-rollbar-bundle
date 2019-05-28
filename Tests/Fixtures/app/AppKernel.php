<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use SymfonyRollbarBundle\SymfonyRollbarBundle;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SymfonyRollbarBundle(),
        ];

        return $bundles;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    public function getProjectDir()
    {
        return $this->getRootDir();
    }

    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $path = $this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml';
        $loader->load($path);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/var/' . $this->getEnvironment() . '/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/var/' . $this->getEnvironment() . '/logs';
    }
}
