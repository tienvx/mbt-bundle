<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepository;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Repository\BugRepository
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class BugRepositoryTest extends TestCase
{
    protected EntityManagerDecorator $manager;
    protected BugInterface $bug;
    protected BugRepositoryInterface $bugRepository;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(EntityManagerDecorator::class);
        $this->manager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(Bug::class)
            ->willReturn($this->createMock(ClassMetadata::class));
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(Bug::class)
            ->willReturn($this->manager);
        $this->bugRepository = new BugRepository($managerRegistry);
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $this->bug = new Bug();
        $this->bug->setProgress($progress);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
    }

    public function testUpdateSteps(): void
    {
        $newSteps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->never())->method('lock');
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->updateSteps($this->bug, $newSteps);
        $this->assertNotSame($newSteps, $this->bug->getSteps());
        $this->assertSame(5, $this->bug->getProgress()->getProcessed());
        $this->assertSame(10, $this->bug->getProgress()->getTotal());
    }

    public function testUpdateStepsWithShorterSteps(): void
    {
        $newSteps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->updateSteps($this->bug, $newSteps);
        $this->assertSame($newSteps, $this->bug->getSteps());
        $this->assertSame(0, $this->bug->getProgress()->getProcessed());
        $this->assertSame(0, $this->bug->getProgress()->getTotal());
    }

    public function testIncreaseProcessed(): void
    {
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->increaseProcessed($this->bug, 2);
        $this->assertSame(7, $this->bug->getProgress()->getProcessed());
        $this->assertSame(10, $this->bug->getProgress()->getTotal());
    }

    public function testIncreaseProcessedReachLimit(): void
    {
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->increaseProcessed($this->bug, 6);
        $this->assertSame(10, $this->bug->getProgress()->getProcessed());
        $this->assertSame(10, $this->bug->getProgress()->getTotal());
    }

    public function testIncreaseTotal(): void
    {
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->increaseTotal($this->bug, 3);
        $this->assertSame(5, $this->bug->getProgress()->getProcessed());
        $this->assertSame(13, $this->bug->getProgress()->getTotal());
    }
}