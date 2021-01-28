<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
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
        $this->command = new Command();
        $this->command->setCommand(WindowCommandRunner::OPEN);
        $this->command->setTarget('http://localhost:1234');
        $this->command->setValue('123');
    }

    public function testSerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:52:"Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command":3:{s:7:"command";s:4:"open";s:6:"target";s:21:"http://localhost:1234";s:5:"value";s:3:"123";}', serialize($this->command));
    }

    public function testUnerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $command = unserialize('O:52:"Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command":3:{s:7:"command";s:5:"click";s:6:"target";s:11:"css=.button";s:5:"value";N;}');
        $this->assertInstanceOf(CommandInterface::class, $command);
        $this->assertSame(MouseCommandRunner::CLICK, $command->getCommand());
        $this->assertSame('css=.button', $command->getTarget());
        $this->assertSame(null, $command->getValue());
    }
}
