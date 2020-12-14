<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\ValueObject\Progress;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\BugProgress;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\BugProgress
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class BugProgressTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $this->bug = new Bug();
        $this->bug->setProgress($progress);
    }

    public function testIncreaseProcessed(): void
    {
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager
            ->expects($this->once())
            ->method('transactional')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $bugProgress = new BugProgress($this->entityManager);
        $bugProgress->increaseProcessed($this->bug, 2);
        $this->assertSame(7, $this->bug->getProgress()->getProcessed());
        $this->assertSame(10, $this->bug->getProgress()->getTotal());
    }

    public function testIncreaseProcessedReachLimit(): void
    {
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager
            ->expects($this->once())
            ->method('transactional')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $bugProgress = new BugProgress($this->entityManager);
        $bugProgress->increaseProcessed($this->bug, 6);
        $this->assertSame(10, $this->bug->getProgress()->getProcessed());
        $this->assertSame(10, $this->bug->getProgress()->getTotal());
    }

    public function testIncreaseTotal(): void
    {
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager
            ->expects($this->once())
            ->method('transactional')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $bugProgress = new BugProgress($this->entityManager);
        $bugProgress->increaseTotal($this->bug, 3);
        $this->assertSame(5, $this->bug->getProgress()->getProcessed());
        $this->assertSame(13, $this->bug->getProgress()->getTotal());
    }
}
