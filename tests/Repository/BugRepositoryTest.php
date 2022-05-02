<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Repository;

use Doctrine\DBAL\Connection;
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
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Video
 */
class BugRepositoryTest extends TestCase
{
    protected EntityManagerDecorator $manager;
    protected BugInterface $bug;
    protected BugRepositoryInterface $bugRepository;
    protected Connection $connection;

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
        $this->connection = $this->createMock(Connection::class);
    }

    /**
     * @dataProvider stepsProvider
     */
    public function testUpdateSteps(int $length, int $expectedLength, int $expectedProcessed, int $expectedTotal): void
    {
        $newSteps = array_map(fn () => $this->createMock(StepInterface::class), range(0, $length - 1));
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager
            ->expects($this->exactly(count($this->bug->getSteps()) !== $expectedLength))
            ->method('lock')
            ->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->manager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->bugRepository->updateSteps($this->bug, $newSteps);
        $this->assertCount($expectedLength, $this->bug->getSteps());
        $this->assertSame($expectedProcessed, $this->bug->getProgress()->getProcessed());
        $this->assertSame($expectedTotal, $this->bug->getProgress()->getTotal());
    }

    public function stepsProvider(): array
    {
        return [
            [4, 3, 5, 10],
            [3, 3, 5, 10],
            [2, 2, 0, 0],
        ];
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

    public function testStartRecordingBug(): void
    {
        $this->bug->getVideo()->setRecording(false);
        $this->manager->expects($this->once())->method('refresh')->with($this->bug);
        $this->manager->expects($this->once())->method('flush');
        $this->bugRepository->startRecording($this->bug);
        $this->assertTrue($this->bug->getVideo()->isRecording());
    }

    public function testStopRecordingBug(): void
    {
        $this->connection->expects($this->once())->method('connect');
        $this->bug->getVideo()->setRecording(true);
        $this->manager->expects($this->once())->method('flush');
        $this->manager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->bugRepository->stopRecording($this->bug);
        $this->assertFalse($this->bug->getVideo()->isRecording());
    }
}
