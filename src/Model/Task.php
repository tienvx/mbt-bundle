<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\BrowserInterface;

class Task extends Debug implements TaskInterface
{
    protected const TASK = 'task';

    protected ?int $id = null;
    protected string $title = '';
    protected RevisionInterface $modelRevision;
    protected ?int $author = null;
    protected bool $running = false;
    protected BrowserInterface $browser;
    protected Collection $bugs;
    protected DateTimeInterface $updatedAt;
    protected DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->bugs = new ArrayCollection();
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

    public function getModelRevision(): RevisionInterface
    {
        return $this->modelRevision;
    }

    public function setModelRevision(RevisionInterface $modelRevision): void
    {
        $this->modelRevision = $modelRevision;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(?int $author): void
    {
        $this->author = $author;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function setRunning(bool $running): void
    {
        $this->running = $running;
    }

    public function getBrowser(): BrowserInterface
    {
        return $this->browser;
    }

    public function setBrowser(BrowserInterface $browser): void
    {
        $this->browser = $browser;
    }

    /**
     * @return Collection<BugInterface>
     */
    public function getBugs(): Collection
    {
        return $this->bugs;
    }

    public function addBug(BugInterface $bug): void
    {
        if (!$this->bugs->contains($bug)) {
            $this->bugs->add($bug);
            $bug->setTask($this);
        }
    }

    public function getLogName(): string
    {
        return sprintf('%s-%d.log', static::TASK, $this->getId());
    }

    public function getVideoName(): string
    {
        return sprintf('%s-%d.mp4', static::TASK, $this->getId());
    }

    public function getTask(): TaskInterface
    {
        return $this;
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
