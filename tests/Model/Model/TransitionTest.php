<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Transition;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class TransitionTest extends TestCase
{
    protected TransitionInterface $transition;
    protected CommandInterface $command1;
    protected CommandInterface $command2;

    protected function setUp(): void
    {
        $this->setUpCommands();
        $this->transition = new Transition();
        $this->transition->setGuard('count > 2');
        $this->transition->setFromPlaces([1, 2, 3]);
        $this->transition->setToPlaces([12, 23]);
        $this->transition->setCommands([
            $this->command1,
            $this->command2,
        ]);
    }

    protected function setUpCommands(): void
    {
        $this->command1 = new Command();
        $this->command2 = new Command();
        $this->command1->setCommand(KeyboardCommandRunner::TYPE);
        $this->command1->setTarget('css=.email');
        $this->command1->setValue('test@example.com');
        $this->command2->setCommand(MouseCommandRunner::CLICK);
        $this->command2->setTarget('css=.link');
        $this->command2->setValue(null);
    }

    /**
     * @dataProvider differentTransitionProvider
     */
    public function testIsNotSame(?string $guard, array $fromPlaces, array $toPlaces, array $commands): void
    {
        $transition = new Transition();
        $transition->setGuard($guard);
        $transition->setFromPlaces($fromPlaces);
        $transition->setToPlaces($toPlaces);
        $transition->setCommands($commands);
        $this->assertFalse($transition->isSame($this->transition));
    }

    public function testIsSame(): void
    {
        $transition = new Transition();
        $transition->setGuard('count > 2');
        $transition->setFromPlaces([1, 2, 3]);
        $transition->setToPlaces([12, 23]);
        $transition->setCommands([
            $this->command1,
            $this->command2,
        ]);
        $this->assertTrue($transition->isSame($this->transition));
    }

    public function differentTransitionProvider(): array
    {
        $this->setUpCommands();
        $command = new Command();
        $command->setCommand(KeyboardCommandRunner::TYPE);
        $command->setTarget('css=.name');
        $command->setValue('My name');

        return [
            ['count > 2', [1, 2, 3], [12, 23], [$this->command1]],
            ['count > 2', [1, 2, 3], [12, 23], [$this->command2]],
            ['count > 2', [1, 2, 3], [12, 23], [$this->command1, $command]],
            ['count > 2', [1, 2, 3], [12], [$this->command1, $this->command2]],
            ['count > 2', [1, 2, 3], [23], [$this->command1, $this->command2]],
            ['count > 2', [1, 2], [12, 23], [$this->command1, $this->command2]],
            ['count <= 2', [1, 2, 3], [12, 23], [$this->command1, $this->command2]],
        ];
    }
}
