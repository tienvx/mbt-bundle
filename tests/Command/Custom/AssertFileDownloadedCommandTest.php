<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Custom;

use Exception;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertFileDownloadedCommand;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Exception\HttpClientException;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AssertFileDownloadedCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractHasHttpClientCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractCustomCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertFileDownloadedCommandTest extends HasHttpClientCommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'File name';
    protected string $valueHelper = '';
    protected string $group = 'custom';

    protected function createCommand(): AssertFileDownloadedCommand
    {
        return new AssertFileDownloadedCommand($this->httpClient);
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(ResponseInterface|HttpClientException $response, ?Exception $exception): void
    {
        $this->setUpUrl();
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->sessionId);
        $mock = $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->webdriverUri . '/download/' . $this->sessionId . '/file.txt'
            );
        if ($response instanceof ResponseInterface) {
            $mock->willReturn($response);
        } else {
            $mock->willThrowException($response);
        }
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->command->run('file.txt', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [$this->createResponse(200), null],
            [$this->createResponse(404), new Exception('Failed expecting that file file.txt is downloaded')],
            [
                new HttpClientException('Something wrong'),
                new RuntimeException('Can not get downloaded file file.txt: Something wrong'),
            ],
        ];
    }

    protected function createResponse(int $code): ResponseInterface
    {
        $mock = $this->createMock(ResponseInterface::class);
        $mock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn($code);

        return $mock;
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', true],
        ];
    }

    public function valueProvider(): array
    {
        return [
            [null, true],
            ['', true],
            ['anything', true],
        ];
    }
}
