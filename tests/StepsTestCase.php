<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

abstract class StepsTestCase extends TestCase
{
    public function assertSteps(array $expected, array $actual): void
    {
        foreach ($actual as $index => $step) {
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertStep($expected[$index], $step);
        }
    }

    public function assertStep(StepInterface $expected, StepInterface $actual): void
    {
        $this->assertSame($expected->getPlaces(), $actual->getPlaces());
        $this->assertSame($expected->getColor()->getValues(), $actual->getColor()->getValues());
        $this->assertSame($expected->getTransition(), $actual->getTransition());
    }
}
