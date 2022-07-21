<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Custom;

use Exception;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tienvx\Bundle\MbtBundle\Command\Custom\UpdateClipboardCommand;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Exception\HttpClientException;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\UpdateClipboardCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractHasHttpClientCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractCustomCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class UpdateClipboardCommandTest extends HasHttpClientCommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Clipboard content';
    protected string $valueHelper = '';
    protected string $group = 'custom';

    protected function createCommand(): UpdateClipboardCommand
    {
        return new UpdateClipboardCommand($this->httpClient);
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
                'POST',
                $this->webdriverUri . '/clipboard/' . $this->sessionId,
                ['body' => 'clipboard']
            );
        if ($response instanceof ResponseInterface) {
            $mock->willReturn($response);
        } else {
            $mock->willThrowException($response);
        }
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->command->run('clipboard', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [$this->createResponse(123), null],
            [
                new HttpClientException('Something wrong'),
                new RuntimeException('Can not update clipboard: Something wrong'),
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
            ['', true],
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
