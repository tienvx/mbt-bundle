<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class UpdateClipboardCommand extends AbstractHasHttpClientCommand
{
    public static function getTargetHelper(): string
    {
        return 'Clipboard content';
    }

    public static function validateTarget(?string $target): bool
    {
        return !is_null($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        try {
            $this->httpClient->request(
                'POST',
                $this->getUrl('clipboard', $driver),
                ['body' => $target]
            )->getStatusCode();
        } catch (ExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Can not update clipboard: %s',
                $e->getMessage()
            ));
        }
    }
}
