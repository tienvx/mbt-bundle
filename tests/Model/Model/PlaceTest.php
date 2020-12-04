<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
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
    protected CommandInterface $assertion1;
    protected CommandInterface $assertion2;

    protected function setUp(): void
    {
        $this->setUpAssertions();
        $this->place = new Place();
        $this->place->setInit(true);
        $this->place->setAssertions([
            $this->assertion1,
            $this->assertion2,
        ]);
    }

    protected function setUpAssertions(): void
    {
        $this->assertion1 = new Command();
        $this->assertion2 = new Command();
        $this->assertion1->setCommand(CommandInterface::ASSERT_TEXT);
        $this->assertion1->setTarget('css=.title');
        $this->assertion1->setValue('Hello');
        $this->assertion2->setCommand(CommandInterface::ASSERT_ALERT);
        $this->assertion2->setTarget('css=.warning');
        $this->assertion2->setValue('Are you sure?');
    }

    /**
     * @dataProvider placeProvider
     */
    public function testIsNotSame(bool $init, array $assertions): void
    {
        $place = new Place();
        $place->setInit($init);
        $place->setAssertions($assertions);
        $this->assertFalse($place->isSame($this->place));
    }

    public function testIsSame(): void
    {
        $place = new Place();
        $place->setInit(true);
        $place->setAssertions([
            $this->assertion1,
            $this->assertion2,
        ]);
        $this->assertTrue($place->isSame($this->place));
    }

    public function placeProvider(): array
    {
        $this->setUpAssertions();
        $assertion = new Command();
        $assertion->setCommand(CommandInterface::ASSERT_ALERT);
        $assertion->setTarget('css=.warning');
        $assertion->setValue('Are you sure about this?');

        return [
            [false, [$this->assertion1, $this->assertion2]],
            [true, [$this->assertion1]],
            [true, [$this->assertion2]],
            [false, [$this->assertion1, $assertion]],
        ];
    }
}
