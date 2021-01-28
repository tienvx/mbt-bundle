<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

interface BugInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    /**
     * @return StepInterface[]
     */
    public function getSteps(): array;

    public function setSteps(StepInterface ...$steps): void;

    public function getTask(): TaskInterface;

    public function setTask(TaskInterface $task): void;

    public function getMessage(): string;

    public function setMessage(string $message): void;

    public function getProgress(): ProgressInterface;

    public function setProgress(ProgressInterface $progress): void;

    public function isClosed(): bool;

    public function setClosed(bool $closed): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): DateTimeInterface;
}
