<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model\Revision;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CommandTest extends TestCase
{
    protected CommandInterface $command;

    protected function setUp(): void
    {
        $this->command = $this->createCommand();
        $this->command->setCommand('open');
        $this->command->setTarget('http://localhost:1234');
        $this->command->setValue('123');
    }

    public function testSerialize(): void
    {
        $className = get_class($this->command);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:' . strlen($className) . ':"' . $className . '":3:{s:7:"command";s:4:"open";s:6:"target";s:21:"http://localhost:1234";s:5:"value";s:3:"123";}', serialize($this->command));
    }

    public function testUnerialize(): void
    {
        $className = get_class($this->command);
        // phpcs:ignore Generic.Files.LineLength
        $command = unserialize('O:' . strlen($className) . ':"' . $className . '":3:{s:7:"command";s:5:"click";s:6:"target";s:11:"css=.button";s:5:"value";N;}');
        $this->assertInstanceOf(CommandInterface::class, $command);
        $this->assertSame('click', $command->getCommand());
        $this->assertSame('css=.button', $command->getTarget());
        $this->assertSame(null, $command->getValue());
    }

    protected function createCommand(): CommandInterface
    {
        return new Command();
    }
}
