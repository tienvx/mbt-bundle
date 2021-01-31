<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

abstract class Model implements ModelInterface
{
    protected ?int $id = null;
    protected ?int $author = null;
    protected string $label = '';
    protected ?string $tags = null;
    protected Collection $revisions;
    protected RevisionInterface $activeRevision;
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

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(?int $author): void
    {
        $this->author = $author;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return Collection|RevisionInterface[]
     */
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(RevisionInterface $revision): void
    {
        if (!$this->revisions->contains($revision)) {
            $this->revisions->add($revision);
            $revision->setModel($this);
        }
    }

    public function removeRevision(RevisionInterface $revision): void
    {
        if ($this->revisions->contains($revision)) {
            $this->revisions->removeElement($revision);
            $revision->setModel(null);
        }
    }

    public function getActiveRevision(): RevisionInterface
    {
        return $this->activeRevision;
    }

    public function setActiveRevision(RevisionInterface $activeRevision): void
    {
        $this->activeRevision = $activeRevision;
        $this->addRevision($activeRevision);
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

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'tags' => $this->tags,
        ] + $this->activeRevision->toArray();
    }
}
