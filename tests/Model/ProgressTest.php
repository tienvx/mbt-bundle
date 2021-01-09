<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class ProgressTest extends TestCase
{
    protected ProgressInterface $progress;

    protected function setUp(): void
    {
        $this->progress = new Progress();
        $this->progress->setTotal(10);
        $this->progress->setProcessed(5);
    }

    public function testIncreaseProcessed(): void
    {
        $this->progress->increase(2);
        $this->assertSame(7, $this->progress->getProcessed());
        $this->assertSame(10, $this->progress->getTotal());
    }

    public function testIncreaseProcessedReachLimit(): void
    {
        $this->progress->increase(6);
        $this->assertSame(10, $this->progress->getProcessed());
        $this->assertSame(10, $this->progress->getTotal());
    }

    public function testSetTotal(): void
    {
        $this->progress->setTotal(15);
        $this->assertSame(5, $this->progress->getProcessed());
        $this->assertSame(15, $this->progress->getTotal());
    }
}
