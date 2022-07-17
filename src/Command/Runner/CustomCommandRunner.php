<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Exception;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CustomCommandRunner extends CommandRunner
{
    public const UPLOAD = 'upload';
    public const ASSERT_FILE_DOWNLOADED = 'assertFileDownloaded';
    public const ASSERT_CLIPBOARD = 'assertClipboard';
    public const UPDATE_CLIPBOARD = 'updateClipboard';

    protected string $uploadDir;
    protected string $webdriverUri;

    public function __construct(protected HttpClientInterface $httpClient)
    {
    }

    public function setUploadDir(string $uploadDir): void
    {
        $this->uploadDir = $uploadDir;
    }

    public function setWebdriverUri(string $webdriverUri): void
    {
        $this->webdriverUri = $webdriverUri;
    }

    public function getAllCommands(): array
    {
        return [
            self::UPLOAD,
            self::ASSERT_FILE_DOWNLOADED,
            self::ASSERT_CLIPBOARD,
            self::UPDATE_CLIPBOARD,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return $this->getAllCommands();
    }

    public function getCommandsRequireValue(): array
    {
        return [
            self::UPLOAD,
        ];
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
            case self::ASSERT_FILE_DOWNLOADED:
                try {
                    $code = $this->httpClient->request(
                        'GET',
                        sprintf('%s/%s', $this->getUrl('download', $driver), $command->getTarget())
                    )->getStatusCode();
                    if (200 !== $code) {
                        throw new Exception(sprintf(
                            'Failed expecting that file %s is downloaded',
                            $command->getTarget()
                        ));
                    }
                } catch (ExceptionInterface $e) {
                    throw new RuntimeException(sprintf(
                        'Can not get downloaded file %s: %s',
                        $command->getTarget(),
                        $e->getMessage()
                    ));
                }
                break;
            case self::ASSERT_CLIPBOARD:
                try {
                    $clipboard = $this->httpClient->request('GET', $this->getUrl('clipboard', $driver))->getContent();
                    if ($command->getTarget() !== $clipboard) {
                        throw new Exception(sprintf(
                            "Failed expecting that clipboard's content equals '%s', actual value '%s'",
                            $command->getTarget(),
                            $clipboard
                        ));
                    }
                } catch (ExceptionInterface $e) {
                    throw new RuntimeException(sprintf(
                        'Can not get clipboard: %s',
                        $e->getMessage()
                    ));
                }
                break;
            case self::UPDATE_CLIPBOARD:
                try {
                    $this->httpClient->request(
                        'POST',
                        $this->getUrl('clipboard', $driver),
                        ['body' => $command->getTarget()]
                    )->getStatusCode();
                } catch (ExceptionInterface $e) {
                    throw new RuntimeException(sprintf(
                        'Can not update clipboard: %s',
                        $e->getMessage()
                    ));
                }
                break;
            default:
                break;
        }
    }

    protected function getFilePath(CommandInterface $command): string
    {
        return $this->uploadDir . DIRECTORY_SEPARATOR . (string) $command->getValue();
    }

    protected function getUrl(string $type, RemoteWebDriver $driver): string
    {
        return sprintf('%s/%s/%s', rtrim($this->webdriverUri, '/'), $type, $driver->getSessionID());
    }
}
