<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

interface TaskInterface
{
    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function getWorkflow(): WorkflowInterface;

    public function setWorkflow(WorkflowInterface $workflow): self;

    public function getGenerator(): GeneratorInterface;

    public function setGenerator(GeneratorInterface $generator): self;

    public function setGeneratorOptions(GeneratorOptionsInterface $generatorOptions): self;

    public function getGeneratorOptions(): GeneratorOptionsInterface;

    public function getReducer(): ReducerInterface;

    public function setReducer(ReducerInterface $reducer): self;

    public function getReporters(): array;

    public function setReporters(array $reporters): self;

    public function getStatus(): string;

    public function setStatus(string $status): self;

    public function addBug(Bug $bug): self;

    public function removeBug(Bug $bug): self;

    public function getBugs(): Collection;

    public function getTakeScreenshots(): bool;

    public function setTakeScreenshots(bool $takeScreenshots): self;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?DateTimeInterface;
}
