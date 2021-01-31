<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface ModelInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getAuthor(): ?int;

    public function setAuthor(?int $author): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getTags(): ?string;

    public function setTags(?string $tags): void;

    public function getRevisions(): Collection;

    public function addRevision(RevisionInterface $revision): void;

    public function removeRevision(RevisionInterface $revision): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getActiveRevision(): RevisionInterface;

    public function setActiveRevision(RevisionInterface $activeRevision): void;

    public function getCreatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function toArray(): array;
}
