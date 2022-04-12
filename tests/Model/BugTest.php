<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Video;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Progress
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class BugTest extends TestCase
{
    protected BugInterface $bug;
    protected array $steps;
    protected TaskInterface $task;
    protected ProgressInterface $progress;
    protected Bug\VideoInterface $video;

    protected function setUp(): void
    {
        $this->steps = [
            new Step([], new Color(), 0),
            new Step([], new Color(), 1),
        ];
        $this->task = new Task();
        $this->progress = new Progress();
        $this->video = new Video();
        $this->bug = $this->createBug();
        $this->bug->setId(123);
        $this->bug->setTitle('bug title');
        $this->bug->setSteps($this->steps);
        $this->bug->setTask($this->task);
        $this->bug->setMessage('bug message');
        $this->bug->setProgress($this->progress);
        $this->bug->setVideo($this->video);
        $this->bug->setClosed(true);
    }

    public function testProperties(): void
    {
        $this->assertSame(123, $this->bug->getId());
        $this->assertSame('bug title', $this->bug->getTitle());
        $this->assertSame($this->steps, $this->bug->getSteps());
        $this->assertSame($this->task, $this->bug->getTask());
        $this->assertSame('bug message', $this->bug->getMessage());
        $this->assertSame($this->progress, $this->bug->getProgress());
        $this->assertSame(true, $this->bug->isClosed());
        $this->assertSame($this->video, $this->bug->getVideo());
    }

    protected function createBug(): BugInterface
    {
        return new Bug();
    }
}
