<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Custom;

use Exception;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertClipboardCommand;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Exception\HttpClientException;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AssertClipboardCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractHasHttpClientCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractCustomCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertClipboardCommandTest extends HasHttpClientCommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Expected clipboard content';
    protected string $valueHelper = '';
    protected string $clipboard = 'text 1';
    protected string $group = 'custom';

    protected function createCommand(): AssertClipboardCommand
    {
        return new AssertClipboardCommand($this->httpClient);
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
                $this->webdriverUri . '/clipboard/' . $this->sessionId
            );
        if ($response instanceof ResponseInterface) {
            $mock->willReturn($response);
        } else {
            $mock->willThrowException($response);
        }
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->command->run($this->clipboard, null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [$this->createResponse($this->clipboard), null],
            [$this->createResponse('text 2'), new Exception(
                "Failed expecting that clipboard's content equals '{$this->clipboard}', actual value 'text 2'"
            )],
            [
                new HttpClientException('Something wrong'),
                new RuntimeException('Can not get clipboard: Something wrong'),
            ],
        ];
    }

    protected function createResponse(string $clipboard): ResponseInterface
    {
        $mock = $this->createMock(ResponseInterface::class);
        $mock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($clipboard);

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
