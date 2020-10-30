<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;

class Task implements TaskInterface
{
    protected ?int $id;

    protected string $title;

    protected ModelInterface $model;

    protected ProgressInterface $progress;

    protected ?DateTimeInterface $updatedAt = null;

    protected ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->progress = new Progress();
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

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function setModel(ModelInterface $model): void
    {
        $this->model = $model;
    }

    public function getProgress(): ProgressInterface
    {
        return $this->progress;
    }

    public function setProgress(ProgressInterface $progress): void
    {
        $this->progress = $progress;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
