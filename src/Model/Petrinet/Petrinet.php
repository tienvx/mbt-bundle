<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\Petrinet as BasePetrinet;

class Petrinet extends BasePetrinet implements PetrinetInterface
{
    protected int $version;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
