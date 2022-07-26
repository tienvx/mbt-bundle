<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\CreateBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\CreateBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\CreateBugMessage
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class CreateBugMessageHandlerTest extends TestCase
{
    protected ConfigInterface|MockObject $config;
    protected TaskRepositoryInterface|MockObject $taskRepository;
    protected CreateBugMessageHandler $handler;
    protected CreateBugMessage $message;
    protected TaskInterface $task;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->handler = new CreateBugMessageHandler($this->config, $this->taskRepository);
        $this->message = new CreateBugMessage(
            123,
            [$this->createMock(StepInterface::class), $this->createMock(StepInterface::class)],
            'Something wrong'
        );
        $this->task = new Task();
        $this->task->setId(123);
    }

    public function testInvokeNoTask(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not create bug for task 123: task not found');
        $this->taskRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        call_user_func($this->handler, $this->message);
    }

    public function testInvoke(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($this->task);
        $this->config
            ->expects($this->once())
            ->method('getDefaultBugTitle')
            ->willReturn('Bug title');
        $this->taskRepository
            ->expects($this->once())
            ->method('addBug')
            ->with($this->task, $this->callback(function ($bug) {
                return $bug instanceof Bug &&
                    'Bug title' === $bug->getTitle() &&
                    $bug->getSteps() === $this->message->getSteps() &&
                    'Something wrong' === $bug->getMessage();
            }));
        call_user_func($this->handler, $this->message);
    }
}
