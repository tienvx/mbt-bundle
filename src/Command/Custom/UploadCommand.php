<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class UploadCommand extends AbstractCustomCommand
{
    public function __construct(protected string $uploadDir)
    {
    }

    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function getValueHelper(): string
    {
        return 'File to upload';
    }

    public function validateValue(?string $value): bool
    {
        return $value && file_exists($this->getFilePath($value));
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver
            ->findElement($this->getSelector($target))
            ->setFileDetector(new LocalFileDetector())
            ->sendKeys($this->getFilePath($value));
    }

    protected function getFilePath(string $value): string
    {
        return $this->uploadDir . DIRECTORY_SEPARATOR . $value;
    }
}
