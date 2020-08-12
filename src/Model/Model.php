<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;

class Model implements ModelInterface
{
    protected ?int $id;

    protected string $label;

    protected int $version = 0;

    protected array $tags = [];

    protected PetrinetInterface $petrinet;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setPetrinet(PetrinetInterface $petrinet): void
    {
        $this->petrinet = $petrinet;
    }

    public function getPetrinet(): PetrinetInterface
    {
        return $this->petrinet;
    }
}
