<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;

interface ModelInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getTags(): array;

    public function setTags(array $tags): void;

    public function addTag(string $tag): void;

    public function setPetrinet(PetrinetInterface $petrinet): void;

    public function getPetrinet(): PetrinetInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?DateTimeInterface;
}
