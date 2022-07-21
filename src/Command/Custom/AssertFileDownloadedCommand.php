<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertFileDownloadedCommand extends AbstractHasHttpClientCommand
{
    public static function getTargetHelper(): string
    {
        return 'File name';
    }

    public static function validateTarget(?string $target): bool
    {
        return !empty($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        try {
            $code = $this->httpClient->request(
                'GET',
                sprintf('%s/%s', $this->getUrl('download', $driver), $target)
            )->getStatusCode();
            $this->assert(200 === $code, sprintf(
                'Failed expecting that file %s is downloaded',
                $target
            ));
        } catch (ExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Can not get downloaded file %s: %s',
                $target,
                $e->getMessage()
            ));
        }
    }
}
