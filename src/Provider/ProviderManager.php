<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ProviderManager extends AbstractPluginManager
{
    protected string $providerName;

    public function setProviderName(string $providerName): void
    {
        $this->providerName = $providerName;
    }

    /**
     * @throws ExceptionInterface
     */
    public function get(string $name): ProviderInterface
    {
        $provider = $this->locator->has($name) ? $this->locator->get($name) : null;
        if ($provider instanceof ProviderInterface) {
            return $provider;
        }

        throw new UnexpectedValueException(sprintf('Provider "%s" does not exist.', $name));
    }

    /**
     * @throws ExceptionInterface
     */
    public function getProvider(): ProviderInterface
    {
        return $this->get($this->providerName);
    }
}
