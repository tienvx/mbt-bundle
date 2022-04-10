<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Model\Revision\TransitionTest as TransitionModelTest;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 */
class TransitionTest extends TransitionModelTest
{
    protected function createTransition(): TransitionInterface
    {
        return new Transition();
    }
}
