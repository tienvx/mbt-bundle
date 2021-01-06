<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ProviderManager extends AbstractPluginManager implements ProviderManagerInterface
{
    protected array $config;

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getProvider(string $name): ProviderInterface
    {
        if ($this->has($name) && ($provider = $this->get($name)) && $provider instanceof ProviderInterface) {
            return $provider;
        }

        throw new UnexpectedValueException(sprintf('Provider "%s" does not exist.', $name));
    }

    public function createDriver(TaskInterface $task, ?int $recordVideoBugId = null): RemoteWebDriver
    {
        $provider = $this->getProvider($task->getSeleniumConfig()->getProvider());

        return RemoteWebDriver::create(
            $provider->getSeleniumServerUrl($this->getSeleniumServer($task->getSeleniumConfig()->getProvider())),
            $provider->getCapabilities($task, $recordVideoBugId)
        );
    }

    public function getProviders(): array
    {
        return array_keys($this->config);
    }

    public function getSeleniumServer(string $provider): string
    {
        return $this->config[$provider][Configuration::SELENIUM_SERVER] ?? '';
    }

    public function getPlatforms(string $provider): array
    {
        return array_keys($this->config[$provider][Configuration::PLATFORMS] ?? []);
    }

    public function getBrowsers(string $provider, string $platform): array
    {
        return array_keys($this->config[$provider][Configuration::PLATFORMS][$platform][Configuration::BROWSERS] ?? []);
    }

    public function getBrowserVersions(string $provider, string $platform, string $browser): array
    {
        // phpcs:ignore Generic.Files.LineLength
        return $this->config[$provider][Configuration::PLATFORMS][$platform][Configuration::BROWSERS][$browser][Configuration::VERSIONS] ?? [];
    }

    public function getResolutions(string $provider, string $platform): array
    {
        return $this->config[$provider][Configuration::PLATFORMS][$platform][Configuration::RESOLUTIONS] ?? [];
    }
}
