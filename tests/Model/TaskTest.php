<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task\Browser;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Task;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task\Browser
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 * @uses \Tienvx\Bundle\MbtBundle\Model\Debug
 */
class TaskTest extends TestCase
{
    protected TaskInterface $task;
    protected array $bugs;
    protected RevisionInterface $revision;
    protected Task\BrowserInterface $browser;

    protected function setUp(): void
    {
        $this->bugs = [
            new Bug(),
        ];
        $this->revision = new Revision();
        $this->browser = new Browser();
        $this->task = $this->createTask();
        $this->task->setId(123);
        $this->task->setTitle('bug title');
        $this->task->setModelRevision($this->revision);
        $this->task->setAuthor(12);
        $this->task->setRunning(true);
        $this->task->setBrowser($this->browser);
        $this->task->addBug($this->bugs[0]);
        $this->task->setDebug(true);
    }

    public function testProperties(): void
    {
        $this->assertSame(123, $this->task->getId());
        $this->assertSame('bug title', $this->task->getTitle());
        $this->assertSame($this->revision, $this->task->getModelRevision());
        $this->assertSame(12, $this->task->getAuthor());
        $this->assertSame(true, $this->task->isRunning());
        $this->assertSame($this->browser, $this->task->getBrowser());
        $this->assertSame($this->bugs, $this->task->getBugs()->toArray());
        $this->assertSame(true, $this->task->isDebug());
    }

    public function testGetLogName(): void
    {
        $this->assertSame('task-123.log', $this->task->getLogName());
    }

    public function testGetVideoName(): void
    {
        $this->assertSame('task-123.mp4', $this->task->getVideoName());
    }

    protected function createTask(): TaskInterface
    {
        return new Task();
    }
}
