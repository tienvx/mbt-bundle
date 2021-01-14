<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Place;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class PlaceTest extends TestCase
{
    protected PlaceInterface $place;
    protected CommandInterface $command1;
    protected CommandInterface $command2;

    protected function setUp(): void
    {
        $this->setUpCommands();
        $this->place = new Place();
        $this->place->setStart(true);
        $this->place->setCommands([
            $this->command1,
            $this->command2,
        ]);
    }

    protected function setUpCommands(): void
    {
        $this->command1 = new Command();
        $this->command2 = new Command();
        $this->command1->setCommand(AssertionRunner::ASSERT_TEXT);
        $this->command1->setTarget('css=.title');
        $this->command1->setValue('Hello');
        $this->command2->setCommand(AssertionRunner::ASSERT_ALERT);
        $this->command2->setTarget('css=.warning');
        $this->command2->setValue('Are you sure?');
    }

    /**
     * @dataProvider placeProvider
     */
    public function testIsNotSame(bool $start, array $commands): void
    {
        $place = new Place();
        $place->setStart($start);
        $place->setCommands($commands);
        $this->assertFalse($place->isSame($this->place));
    }

    public function testIsSame(): void
    {
        $place = new Place();
        $place->setStart(true);
        $place->setCommands([
            $this->command1,
            $this->command2,
        ]);
        $this->assertTrue($place->isSame($this->place));
    }

    public function placeProvider(): array
    {
        $this->setUpCommands();
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ALERT);
        $command->setTarget('css=.warning');
        $command->setValue('Are you sure about this?');

        return [
            [false, [$this->command1, $this->command2]],
            [true, [$this->command1]],
            [true, [$this->command2]],
            [false, [$this->command1, $command]],
        ];
    }
}
