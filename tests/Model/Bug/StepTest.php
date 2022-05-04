<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Bug;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepTest extends TestCase
{
    protected StepInterface $step;
    protected CommandInterface $command1;
    protected CommandInterface $command2;
    protected array $places = [0 => 1, 1 => 2];
    protected ColorInterface $color;
    protected int $transition = 123;

    protected function setUp(): void
    {
        $this->color = new Color(['key' => 'value']);
        $this->step = $this->createStep();
    }

    public function testSerialize(): void
    {
        $className = get_class($this->step);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:' . strlen($className) . ':"' . $className . '":3:{s:5:"color";a:1:{s:3:"key";s:5:"value";}s:6:"places";a:2:{i:0;i:1;i:1;i:2;}s:10:"transition";i:123;}', serialize($this->step));
    }

    public function testUnerialize(): void
    {
        $className = get_class($this->step);
        // phpcs:ignore Generic.Files.LineLength
        $step = unserialize('O:' . strlen($className) . ':"' . $className . '":3:{s:5:"color";a:1:{s:4:"key1";s:6:"value2";}s:6:"places";a:2:{i:0;i:3;i:1;i:4;}s:10:"transition";i:234;}');
        $this->assertInstanceOf(StepInterface::class, $step);
        $this->assertSame(['key1' => 'value2'], $step->getColor()->getValues());
        $this->assertSame([3, 4], $step->getPlaces());
        $this->assertSame(234, $step->getTransition());
    }

    public function testClone(): void
    {
        $step = clone $this->step;
        $this->assertNotSame($this->step->getColor(), $step->getColor());
        $this->assertSame($this->step->getColor()->getValues(), $step->getColor()->getValues());
    }

    /**
     * @dataProvider nodeIdProvider
     */
    public function testGetUniqueNodeId(?array $places, string $id): void
    {
        if ($places) {
            $this->step->setPlaces($places);
        }
        $this->assertSame($id, $this->step->getUniqueNodeId());
    }

    public function nodeIdProvider(): array
    {
        return [
            [null, '89fefb193877ee62e29d1da5975dcc47'],
            [[0 => 2, 1 => 1], '02878487ecf2302bf7ba2cc919514889'],
        ];
    }

    protected function createStep(): StepInterface
    {
        return new Step($this->places, $this->color, $this->transition);
    }
}
