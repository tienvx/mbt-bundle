<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Bug;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class BugHelperTest extends TestCase
{
    public function testCreateBug(): void
    {
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $helper = new BugHelper();
        $bug = $helper->createBug($steps, 'Something wrong');
        $this->assertInstanceOf(BugInterface::class, $bug);
        $this->assertSame($steps, $bug->getSteps());
        $this->assertSame('', $bug->getTitle());
        $this->assertSame('Something wrong', $bug->getMessage());
    }
}
