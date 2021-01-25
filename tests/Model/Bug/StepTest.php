<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Bug;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepTest extends TestCase
{
    protected StepInterface $step;
    protected CommandInterface $command1;
    protected CommandInterface $command2;

    protected function setUp(): void
    {
        $this->step = new Step([1, 2], new Color(['key' => 'value']), 123);
    }

    public function testSerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:44:"Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step":3:{s:5:"color";a:1:{s:3:"key";s:5:"value";}s:6:"places";a:2:{i:0;i:1;i:1;i:2;}s:10:"transition";i:123;}', serialize($this->step));
    }

    public function testUnerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $step = unserialize('O:44:"Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step":3:{s:5:"color";a:1:{s:4:"key1";s:6:"value2";}s:6:"places";a:2:{i:0;i:3;i:1;i:4;}s:10:"transition";i:234;}');
        $this->assertInstanceOf(StepInterface::class, $step);
        $this->assertSame(['key1' => 'value2'], $step->getColor()->getValues());
        $this->assertSame([3, 4], $step->getPlaces());
        $this->assertSame(234, $step->getTransition());
    }
}
