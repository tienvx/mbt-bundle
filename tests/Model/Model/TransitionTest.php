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
    protected CommandInterface $action1;
    protected CommandInterface $action2;

    protected function setUp(): void
    {
        $this->setUpActions();
        $this->transition = new Transition();
        $this->transition->setGuard('count > 2');
        $this->transition->setExpression('{product: 1}');
        $this->transition->setFromPlaces([1, 2, 3]);
        $this->transition->setToPlaces([12, 23]);
        $this->transition->setActions([
            $this->action1,
            $this->action2,
        ]);
    }

    protected function setUpActions(): void
    {
        $this->action1 = new Command();
        $this->action2 = new Command();
        $this->action1->setCommand(KeyboardCommandRunner::TYPE);
        $this->action1->setTarget('css=.email');
        $this->action1->setValue('test@example.com');
        $this->action2->setCommand(MouseCommandRunner::CLICK);
        $this->action2->setTarget('css=.link');
        $this->action2->setValue(null);
    }

    /**
     * @dataProvider differentTransitionProvider
     */
    public function testIsNotSame(?string $guard, array $fromPlaces, array $toPlaces, array $actions): void
    {
        $transition = new Transition();
        $transition->setGuard($guard);
        $transition->setFromPlaces($fromPlaces);
        $transition->setToPlaces($toPlaces);
        $transition->setActions($actions);
        $this->assertFalse($transition->isSame($this->transition));
    }

    public function testIsSame(): void
    {
        $transition = new Transition();
        $transition->setGuard('count > 2');
        $transition->setFromPlaces([1, 2, 3]);
        $transition->setToPlaces([12, 23]);
        $transition->setActions([
            $this->action1,
            $this->action2,
        ]);
        $this->assertTrue($transition->isSame($this->transition));
    }

    public function differentTransitionProvider(): array
    {
        $this->setUpActions();
        $action = new Command();
        $action->setCommand(KeyboardCommandRunner::TYPE);
        $action->setTarget('css=.name');
        $action->setValue('My name');

        return [
            ['count > 2', [1, 2, 3], [12, 23], [$this->action1]],
            ['count > 2', [1, 2, 3], [12, 23], [$this->action2]],
            ['count > 2', [1, 2, 3], [12, 23], [$this->action1, $action]],
            ['count > 2', [1, 2, 3], [12], [$this->action1, $this->action2]],
            ['count > 2', [1, 2, 3], [23], [$this->action1, $this->action2]],
            ['count > 2', [1, 2], [12, 23], [$this->action1, $this->action2]],
            ['count <= 2', [1, 2, 3], [12, 23], [$this->action1, $this->action2]],
        ];
    }
}
