<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            new AppBundle\AppBundle()
        ];

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        if ($this->getEnvironment() === 'test'){
            return dirname(__DIR__).'/var/cache/test';
        } else {
            return dirname(__DIR__).'/var/cache/default';
        }
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->getEnvironment() === 'test'){
            $loader->load($this->getRootDir().'/config/config_test.yml');
        } else {
            $loader->load($this->getRootDir().'/config/config.yml');
        }
    }
}
