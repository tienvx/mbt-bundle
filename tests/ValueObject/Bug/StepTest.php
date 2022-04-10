<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Bug;

use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Bug\StepTest as StepModelTest;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepTest extends StepModelTest
{
    protected function createStep(): StepInterface
    {
        return new Step($this->places, $this->color, $this->transition);
    }
}
