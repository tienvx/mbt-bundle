<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

class Task implements TaskInterface
{
    protected ?int $id = null;
    protected string $title = '';
    protected RevisionInterface $modelRevision;
    protected ?int $author = null;
    protected bool $running = false;
    protected string $browser = '';
    protected string $browserVersion = '';
    protected Collection $bugs;
    protected DateTimeInterface $updatedAt;
    protected DateTimeInterface $createdAt;

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

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function setBrowser(string $browser): void
    {
        $this->browser = $browser;
    }

    public function getBrowserVersion(): string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(string $browserVersion): void
    {
        $this->browserVersion = $browserVersion;
    }

    /**
     * @return Collection|BugInterface[]
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
