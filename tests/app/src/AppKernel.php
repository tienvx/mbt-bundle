<?php

namespace App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

class AppKernel extends Kernel
{
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles()
    {
        $bundles = array(
            new SecurityBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
            new MonologBundle(),
            new MakerBundle(),
            new TienvxMbtBundle(),
        );

        return $bundles;
    }

    /**
     * @param LoaderInterface $loader
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(dirname(__DIR__) . '/config/config.yaml');
        $loader->load(dirname(__DIR__) . '/config/services.yaml');
        $loader->load(dirname(__DIR__) . '/config/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->environment;
    }

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/log';
    }
}
