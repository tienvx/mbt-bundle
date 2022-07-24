<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertFileDownloadedCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UpdateClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CommandManager implements CommandManagerInterface
{
    protected string $uploadDir;

    public function __construct(protected HttpClientInterface $httpClient)
    {
    }

    public function setUploadDir(string $uploadDir): void
    {
        $this->uploadDir = $uploadDir;
    }

    public function hasCommand(string $command): bool
    {
        return isset(static::COMMANDS[$command]);
    }

    public function isTargetMissing(string $command, ?string $target): bool
    {
        return $this->hasCommand($command) &&
            call_user_func([static::COMMANDS[$command], 'isTargetRequired']) &&
            empty($target);
    }

    public function isTargetNotValid(string $command, ?string $target): bool
    {
        return $this->hasCommand($command) &&
            !call_user_func([static::COMMANDS[$command], 'validateTarget'], $target);
    }

    public function isValueMissing(string $command, ?string $value): bool
    {
        return $this->hasCommand($command) &&
            call_user_func([static::COMMANDS[$command], 'isValueRequired']) &&
            empty($value);
    }

    public function isValueNotValid(string $command, ?string $value): bool
    {
        return $this->hasCommand($command) &&
            !call_user_func([$this->createCommand($command), 'validateValue'], $value);
    }

    public function run(
        string $command,
        ?string $target,
        ?string $value,
        ValuesInterface $values,
        RemoteWebDriver $driver
    ): void {
        $this->createCommand($command)->run(
            $this->process($target, $values),
            $this->process($value, $values),
            $values,
            $driver
        );
    }

    protected function createCommand(string $command): CommandInterface
    {
        switch ($command) {
            case 'upload':
                return new UploadCommand($this->uploadDir);
            case 'assertFileDownloaded':
                return new AssertFileDownloadedCommand($this->httpClient);
            case 'assertClipboard':
                return new AssertClipboardCommand($this->httpClient);
            case 'updateClipboard':
                return new UpdateClipboardCommand($this->httpClient);
            default:
                $className = static::COMMANDS[$command] ?? null;
                if ($className) {
                    return new $className();
                }

                throw new OutOfRangeException(sprintf('Command %s not found', $command));
        }
    }

    protected function process(?string $text, ValuesInterface $values): ?string
    {
        return is_string($text) ? preg_replace_callback(
            '/\$\{(.*?)\}/',
            fn (array $matches): string => $values->getValues()[$matches[1]] ?? $matches[1],
            $text
        ) : null;
    }
}
