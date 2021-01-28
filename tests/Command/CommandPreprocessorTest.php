<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CommandPreprocessorTest extends TestCase
{
    public function testProcess(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT);
        $command->setTarget('${variable}');
        $command->setValue('value');
        $color = new Color();
        $color->setValues([
            'variable' => 'key',
            'key' => 'value',
        ]);
        $preprocessor = new CommandPreprocessor();
        $newCommand = $preprocessor->process($command, $color);
        $this->assertSame(AssertionRunner::ASSERT, $newCommand->getCommand());
        $this->assertSame('key', $newCommand->getTarget());
        $this->assertSame('value', $newCommand->getValue());
    }

    public function testProcessEmpty(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK);
        $command->setTarget('');
        $command->setValue(null);
        $color = new Color();
        $color->setValues([
            'variable' => 'key',
            'key' => 'value',
        ]);
        $preprocessor = new CommandPreprocessor();
        $newCommand = $preprocessor->process($command, $color);
        $this->assertSame(MouseCommandRunner::CLICK, $newCommand->getCommand());
        $this->assertSame('', $newCommand->getTarget());
        $this->assertSame(null, $newCommand->getValue());
    }
}
