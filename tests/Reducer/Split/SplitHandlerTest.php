<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\HandlerTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class SplitHandlerTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new SplitHandler(
            $this->entityManager,
            $this->messageBus,
            $this->stepRunner,
            $this->stepsBuilder,
            $this->bugHelper,
            $this->selenoidHelper
        );
    }
}
