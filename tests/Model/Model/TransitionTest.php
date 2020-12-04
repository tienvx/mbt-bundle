<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlace;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Transition;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\ToPlace
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class TransitionTest extends TestCase
{
    protected TransitionInterface $transition;
    protected CommandInterface $action1;
    protected CommandInterface $action2;
    protected ToPlaceInterface $toPlace1;
    protected ToPlaceInterface $toPlace2;

    protected function setUp(): void
    {
        $this->setUpActions();
        $this->setUpToPlaces();
        $this->transition = new Transition();
        $this->transition->setGuard('count > 2');
        $this->transition->setFromPlaces([1, 2, 3]);
        $this->transition->setToPlaces([
            $this->toPlace1,
            $this->toPlace2,
        ]);
        $this->transition->setActions([
            $this->action1,
            $this->action2,
        ]);
    }

    protected function setUpActions(): void
    {
        $this->action1 = new Command();
        $this->action2 = new Command();
        $this->action1->setCommand(CommandInterface::CLEAR);
        $this->action1->setTarget('css=.email');
        $this->action1->setValue(null);
        $this->action2->setCommand(CommandInterface::CLICK);
        $this->action2->setTarget('css=.link');
        $this->action2->setValue(null);
    }

    protected function setUpToPlaces(): void
    {
        $this->toPlace1 = new ToPlace();
        $this->toPlace2 = new ToPlace();
        $this->toPlace1->setPlace(12);
        $this->toPlace1->setExpression('{product: 1}');
        $this->toPlace2->setPlace(23);
        $this->toPlace2->setExpression(null);
    }

    /**
     * @dataProvider transitionProvider
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
        $transition->setToPlaces([
            $this->toPlace1,
            $this->toPlace2,
        ]);
        $transition->setActions([
            $this->action1,
            $this->action2,
        ]);
        $this->assertTrue($transition->isSame($this->transition));
    }

    public function transitionProvider(): array
    {
        $this->setUpActions();
        $this->setUpToPlaces();
        $action = new Command();
        $action->setCommand(CommandInterface::TYPE);
        $action->setTarget('css=.name');
        $action->setValue('My name');

        return [
            ['count > 2', [1, 2, 3], [$this->toPlace1, $this->toPlace2], [$this->action1]],
            ['count > 2', [1, 2, 3], [$this->toPlace1, $this->toPlace2], [$this->action2]],
            ['count > 2', [1, 2, 3], [$this->toPlace1, $this->toPlace2], [$this->action1, $action]],
            ['count > 2', [1, 2, 3], [$this->toPlace1], [$this->action1, $this->action2]],
            ['count > 2', [1, 2, 3], [$this->toPlace2], [$this->action1, $this->action2]],
            ['count > 2', [1, 2], [$this->toPlace1, $this->toPlace2], [$this->action1, $this->action2]],
            ['count <= 2', [1, 2, 3], [$this->toPlace1, $this->toPlace2], [$this->action1, $this->action2]],
        ];
    }
}
