<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;
use Tienvx\Bundle\MbtBundle\Model\Values;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Model\Values
 */
class CommandPreprocessorTest extends TestCase
{
    /**
     * @dataProvider commandProvider
     */
    public function testProcess(
        string $commandString,
        ?string $target,
        ?string $value,
        array $values,
        ?string $newTarget,
        ?string $newValue
    ): void {
        $command = new Command();
        $command->setCommand($commandString);
        $command->setTarget($target);
        $command->setValue($value);
        $preprocessor = new CommandPreprocessor();
        $newCommand = $preprocessor->process($command, new Values($values));
        $this->assertSame($commandString, $newCommand->getCommand());
        $this->assertSame($newTarget, $newCommand->getTarget());
        $this->assertSame($newValue, $newCommand->getValue());
    }

    public function commandProvider(): array
    {
        return [
            [
                KeyboardCommandRunner::TYPE,
                'xpath=//div[text()=\'${variable1}\']/input',
                '${variable2}',
                [
                    'variable1' => 'value1',
                    'variable2' => 'value2',
                ],
                'xpath=//div[text()=\'value1\']/input',
                'value2',
            ],
            [
                ScriptCommandRunner::EXECUTE_SCRIPT,
                'return ${variable1} + 1',
                'variable1',
                [
                    'variable1' => 123,
                ],
                'return 123 + 1',
                'variable1',
            ],
            [
                StoreCommandRunner::STORE,
                'closed',
                '${key}',
                [
                    'key' => 'status',
                ],
                'closed',
                'status',
            ],
            [
                WindowCommandRunner::OPEN,
                'http://example.com/${path}',
                null,
                [],
                'http://example.com/path',
                null,
            ],
            [
                MouseCommandRunner::CLICK,
                'css=.id',
                null,
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                'css=.id',
                null,
            ],
            [
                'invalid',
                '',
                null,
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                '',
                null,
            ],
        ];
    }
}
