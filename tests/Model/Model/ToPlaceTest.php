<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlace;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlaceInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\ToPlace
 */
class ToPlaceTest extends TestCase
{
    protected ToPlaceInterface $toPlace;

    protected function setUp(): void
    {
        $this->toPlace = new ToPlace();
        $this->toPlace->setPlace(12);
        $this->toPlace->setExpression('{count: count + 1}');
    }

    /**
     * @dataProvider toPlaceProvider
     */
    public function testIsNotSame(int $place, ?string $expression): void
    {
        $toPlace = new ToPlace();
        $toPlace->setPlace($place);
        $toPlace->setExpression($expression);
        $this->assertFalse($toPlace->isSame($this->toPlace));
    }

    public function testIsSame(): void
    {
        $toPlace = new ToPlace();
        $toPlace->setPlace(12);
        $toPlace->setExpression('{count: count + 1}');
        $this->assertTrue($toPlace->isSame($this->toPlace));
    }

    public function toPlaceProvider(): array
    {
        return [
            [12, '{count: count - 1}'],
            [123, '{count: count + 1}'],
            [12, null],
        ];
    }
}
