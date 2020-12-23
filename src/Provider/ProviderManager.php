<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ProviderManager extends AbstractPluginManager
{
    protected string $seleniumServer;
    protected string $providerName;

    public function setSeleniumServer(string $seleniumServer): void
    {
        $this->seleniumServer = $seleniumServer;
    }

    public function getSeleniumServer(): string
    {
        return $this->seleniumServer;
    }

    public function setProviderName(string $providerName): void
    {
        $this->providerName = $providerName;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
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

    /**
     * @throws ExceptionInterface
     */
    public function createDriver(TaskInterface $task, ?int $recordVideoBugId = null): RemoteWebDriver
    {
        $provider = $this->get($this->getProviderName());

        return RemoteWebDriver::create(
            $provider->getSeleniumServerUrl($this->seleniumServer),
            $provider->getCapabilities($task, $recordVideoBugId)
        );
    }
}
