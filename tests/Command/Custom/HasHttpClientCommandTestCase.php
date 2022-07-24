<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Custom;

use Facebook\WebDriver\Remote\HttpCommandExecutor;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

abstract class HasHttpClientCommandTestCase extends CommandTestCase
{
    protected string $webdriverUri = 'http://localhost:4444';
    protected string $sessionId = 'abc123';
    protected HttpCommandExecutor|MockObject $executor;
    protected HttpClientInterface|MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->executor = $this->createMock(HttpCommandExecutor::class);
        parent::setUp();
    }

    protected function setUpUrl(): void
    {
        $this->executor
            ->expects($this->once())
            ->method('getAddressOfRemoteServer')
            ->willReturn($this->webdriverUri . '/wd/hub');
        $this->driver
            ->expects($this->once())
            ->method('getCommandExecutor')
            ->willReturn($this->executor);
    }
}
