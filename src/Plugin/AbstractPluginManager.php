<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;

abstract class AbstractPluginManager implements PluginManagerInterface
{
    protected ServiceLocator $locator;
    protected array $plugins;

    public function __construct(ServiceLocator $locator, array $plugins)
    {
        $this->locator = $locator;
        $this->plugins = $plugins;
    }

    public function all(): array
    {
        return $this->plugins;
    }

    public function has(string $name): bool
    {
        return in_array($name, $this->plugins) && $this->locator->has($name);
    }

    public function get(string $name): PluginInterface
    {
        $plugin = $this->locator->has($name) ? $this->locator->get($name) : null;
        if ($plugin instanceof PluginInterface) {
            return $plugin;
        }

        throw new UnexpectedValueException(sprintf('Plugin "%s" does not exist.', $name));
    }
}
