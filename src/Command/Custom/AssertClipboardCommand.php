<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertClipboardCommand extends AbstractHasHttpClientCommand
{
    public static function getTargetHelper(): string
    {
        return 'Expected clipboard content';
    }

    public static function validateTarget(?string $target): bool
    {
        return !is_null($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        try {
            $clipboard = $this->httpClient->request(
                'GET',
                $this->getUrl('clipboard', $driver)
            )->getContent();
            $this->assert($clipboard === $target, sprintf(
                "Failed expecting that clipboard's content equals '%s', actual value '%s'",
                $target,
                $clipboard
            ));
        } catch (ExceptionInterface $e) {
            throw new RuntimeException(sprintf(
                'Can not get clipboard: %s',
                $e->getMessage()
            ));
        }
    }
}
