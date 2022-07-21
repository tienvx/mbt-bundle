<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandManager;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertFileDownloadedCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UpdateClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\ClickCommand;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Values;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandManager
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Values
 * @uses \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractHasHttpClientCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Keyboard\SendKeysCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMousePointCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand
 */
class CommandManagerTest extends TestCase
{
    protected CommandManager $manager;
    protected HttpClientInterface|MockObject $httpClient;
    protected RemoteWebDriver|MockObject $driver;
    protected ValuesInterface|MockObject $values;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->manager = new CommandManager($this->httpClient);
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->values = $this->createMock(ValuesInterface::class);
    }

    public function testHasCommand(): void
    {
        $this->assertFalse($this->manager->hasCommand('test'));
        $this->assertTrue($this->manager->hasCommand('open'));
    }

    public function testIsTargetMissing(): void
    {
        $this->assertFalse($this->manager->isTargetMissing('sendKeys', 'name=first-name'));
        $this->assertTrue($this->manager->isTargetMissing('sendKeys', ''));
    }

    public function testIsTargetNotValid(): void
    {
        $this->assertTrue($this->manager->isTargetNotValid('sendKeys', '.gender'));
        $this->assertFalse($this->manager->isTargetNotValid('sendKeys', 'id=email'));
    }

    public function testIsValueMissing(): void
    {
        $this->assertFalse($this->manager->isValueMissing('sendKeys', 'First Name'));
        $this->assertTrue($this->manager->isValueMissing('sendKeys', ''));
    }

    public function testIsValueNotValid(): void
    {
        $this->assertTrue($this->manager->isValueNotValid('clickAt', '123x456'));
        $this->assertFalse($this->manager->isValueNotValid('clickAt', '67,89'));
    }

    public function testRunCommand(): void
    {
        $command = 'command';
        $target = 'target';
        $value = 'value';
        $commandObject = $this->createMock(CommandInterface::class);
        $commandObject
            ->expects($this->once())
            ->method('run')
            ->with('processed ' . $target, 'processed ' . $value, $this->values, $this->driver);
        $this->manager = $this->createPartialMock(CommandManager::class, ['createCommand', 'process']);
        $this->manager
            ->expects($this->once())
            ->method('createCommand')
            ->with($command)
            ->willReturn($commandObject);
        $this->manager
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [$target, $this->values],
                [$value, $this->values]
            )
            ->willReturnCallback(fn (string $text) => 'processed ' . $text);
        $this->manager->run($command, $target, $value, $this->values, $this->driver);
    }

    /**
     * @dataProvider textProvider
     */
    public function testProcess(?string $text, array $values, ?string $expected): void
    {
        $method = (new \ReflectionMethod($this->manager, 'process'));
        $this->assertSame($expected, $method->invoke($this->manager, $text, new Values($values)));
    }

    public function textProvider(): array
    {
        return [
            [
                null,
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                null,
            ],
            [
                '',
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                '',
            ],
            [
                'css=.id',
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                'css=.id',
            ],
            [
                '${key}',
                [
                    'key' => 'status',
                ],
                'status',
            ],
            [
                'variable1',
                [
                    'variable1' => 123,
                ],
                'variable1',
            ],
            [
                'xpath=//div[text()=\'${variable1}\']/input',
                [
                    'variable1' => 'value1',
                    'variable2' => 'value2',
                ],
                'xpath=//div[text()=\'value1\']/input',
            ],
            [
                'http://example.com/${path}',
                [],
                'http://example.com/path',
            ],
        ];
    }

    public function testCreateUploadCommand(): void
    {
        $this->manager->setUploadDir('/path/to/upload/files');
        $method = (new \ReflectionMethod($this->manager, 'createCommand'));
        $command = $method->invoke($this->manager, 'upload');
        $property = (new \ReflectionProperty($command, 'uploadDir'));
        $this->assertInstanceOf(UploadCommand::class, $command);
        $this->assertSame('/path/to/upload/files', $property->getValue($command));
    }

    /**
     * @dataProvider commandProvider
     */
    public function testCreateHasHttpClientCommand(string $command, string $class): void
    {
        $method = (new \ReflectionMethod($this->manager, 'createCommand'));
        $command = $method->invoke($this->manager, $command);
        $property = (new \ReflectionProperty($command, 'httpClient'));
        $this->assertInstanceOf($class, $command);
        $this->assertSame($this->httpClient, $property->getValue($command));
    }

    public function commandProvider(): array
    {
        return [
            ['assertFileDownloaded', AssertFileDownloadedCommand::class],
            ['assertClipboard', AssertClipboardCommand::class],
            ['updateClipboard', UpdateClipboardCommand::class],
        ];
    }

    public function testCreateValidCommand(): void
    {
        $method = (new \ReflectionMethod($this->manager, 'createCommand'));
        $this->assertInstanceOf(ClickCommand::class, $method->invoke($this->manager, 'click'));
    }

    public function testCreateInvalidCommand(): void
    {
        $method = (new \ReflectionMethod($this->manager, 'createCommand'));
        $this->expectExceptionObject(new OutOfRangeException('Command newCommand not found'));
        $method->invoke($this->manager, 'newCommand');
    }
}
