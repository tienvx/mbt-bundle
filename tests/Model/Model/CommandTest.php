<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class CommandTest extends TestCase
{
    protected CommandInterface $command;

    protected function setUp(): void
    {
        $this->command = new Command();
        $this->command->setCommand(WindowCommandRunner::OPEN);
        $this->command->setTarget('http://localhost:1234');
        $this->command->setValue('123');
    }

    /**
     * @dataProvider commandProvider
     */
    public function testIsNotSame(string $cmd, string $target, ?string $value): void
    {
        $command = new Command();
        $command->setCommand($cmd);
        $command->setTarget($target);
        $command->setValue($value);
        $this->assertFalse($command->isSame($this->command));
    }

    public function testIsSame(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::OPEN);
        $command->setTarget('http://localhost:1234');
        $command->setValue('123');
        $this->assertTrue($command->isSame($this->command));
    }

    public function commandProvider(): array
    {
        return [
            [WindowCommandRunner::OPEN, 'http://localhost:1234', '124'],
            [WindowCommandRunner::OPEN, 'http://127.0.0.1:8080', '123'],
            [KeyboardCommandRunner::TYPE, 'http://localhost:1234', '124'],
            [MouseCommandRunner::CLICK, 'css=.button', null],
        ];
    }
}
