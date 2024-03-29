<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\Video;
use Tienvx\Bundle\MbtBundle\Model\Bug\VideoInterface;

class Bug extends Debug implements BugInterface
{
    protected const BUG = 'bug';

    protected ?int $id = null;
    protected string $title;
    protected array $steps = [];
    protected TaskInterface $task;
    protected string $message;
    protected ProgressInterface $progress;
    protected bool $closed = false;
    protected VideoInterface $video;
    protected DateTimeInterface $updatedAt;
    protected DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->progress = new Progress();
        $this->video = new Video();
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function setSteps(array $steps): void
    {
        $this->steps = [];
        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    public function addStep(StepInterface $step): void
    {
        $this->steps[] = $step;
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function setTask(TaskInterface $task): void
    {
        $this->task = $task;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getProgress(): ProgressInterface
    {
        return $this->progress;
    }

    public function setProgress(ProgressInterface $progress): void
    {
        $this->progress = $progress;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): void
    {
        $this->closed = $closed;
    }

    public function getVideo(): VideoInterface
    {
        return $this->video;
    }

    public function setVideo(VideoInterface $video): void
    {
        $this->video = $video;
    }

    public function getLogName(): string
    {
        return sprintf('%s-%d.log', static::BUG, $this->getId());
    }

    public function getVideoName(): string
    {
        return sprintf('%s-%d.mp4', static::BUG, $this->getId());
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
