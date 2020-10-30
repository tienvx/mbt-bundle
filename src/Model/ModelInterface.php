<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;

interface ModelInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getVersion(): int;

    public function setVersion(int $version): void;

    public function getTags(): array;

    public function setTags(array $tags): void;

    public function setPetrinet(PetrinetInterface $petrinet): void;

    public function getPetrinet(): PetrinetInterface;
}
