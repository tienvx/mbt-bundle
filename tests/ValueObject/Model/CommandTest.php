<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Command
 */
class CommandTest extends TestCase
{
    protected CommandInterface $command;

    protected function setUp(): void
    {
        $this->command = new Command();
        $this->command->setCommand(MouseCommandRunner::DOUBLE_CLICK);
    }

    public function testToString(): void
    {
        $this->assertSame(MouseCommandRunner::DOUBLE_CLICK, (string) $this->command);
    }
}
