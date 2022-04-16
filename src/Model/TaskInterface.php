<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\BrowserInterface;

interface TaskInterface extends DebugInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getModelRevision(): RevisionInterface;

    public function setModelRevision(RevisionInterface $modelRevision): void;

    public function getAuthor(): ?int;

    public function setAuthor(?int $author): void;

    public function isRunning(): bool;

    public function setRunning(bool $running): void;

    public function getBrowser(): BrowserInterface;

    public function setBrowser(BrowserInterface $browser): void;

    public function getBugs(): Collection;

    public function addBug(BugInterface $bug): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): DateTimeInterface;
}
