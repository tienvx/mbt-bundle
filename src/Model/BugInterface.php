<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

interface BugInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getSteps(): Collection;

    public function setSteps(iterable $steps): void;

    public function addStep(StepInterface $step): void;

    public function removeStep(StepInterface $step): void;

    public function getModel(): ModelInterface;

    public function setModel(ModelInterface $workflow): void;

    public function getMessage(): string;

    public function setMessage(string $bugMessage): void;

    public function getProgress(): ProgressInterface;

    public function setProgress(ProgressInterface $progress): void;

    public function isClosed(): bool;

    public function setClosed(bool $closed): void;

    public function getModelVersion(): int;

    public function setModelVersion(int $modelVersion): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): DateTimeInterface;
}
