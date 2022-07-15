<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CustomCommandRunner extends CommandRunner
{
    public const UPLOAD = 'upload';

    public function __construct(protected string $uploadDir)
    {
    }

    public function getAllCommands(): array
    {
        return [
            self::UPLOAD,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return $this->getAllCommands();
    }

    public function getCommandsRequireValue(): array
    {
        return $this->getAllCommands();
    }

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::UPLOAD:
                $driver
                    ->findElement($this->getSelector($command->getTarget()))
                    ->setFileDetector(new LocalFileDetector())
                    ->sendKeys($this->getFilePath($command));
                break;
            default:
                break;
        }
    }

    protected function getFilePath(CommandInterface $command): string
    {
        return $this->uploadDir . DIRECTORY_SEPARATOR . (string) $command->getValue();
    }
}
