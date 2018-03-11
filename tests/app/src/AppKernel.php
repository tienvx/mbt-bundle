<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new ApiPlatformBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DoctrineFixturesBundle(),
            new TwigBundle(),
            new TienvxMbtBundle(),
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(dirname(__DIR__) . '/config/config.yml');
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
        return dirname(__DIR__) . '/var/logs';
    }
}
