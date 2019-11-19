<?php

namespace App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(dirname(__DIR__).'/config/config.yaml');
        $loader->load(dirname(__DIR__).'/config/services.yaml');
        $loader->load(dirname(__DIR__).'/config/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->environment;
    }

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/log';
    }
}
