<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model\Revision;

use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\HasCommands;

class Transition implements TransitionInterface
{
    use HasCommands;

    protected string $label = '';
    protected ?string $guard = null;
    protected array $fromPlaces = [];
    protected array $toPlaces = [];

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data)
    {
        $this->label = $data['label'];
        $this->guard = $data['guard'];
        $this->fromPlaces = $data['fromPlaces'];
        $this->toPlaces = $data['toPlaces'];
        $this->commands = array_map([CommandFactory::class, 'createFromArray'], $data['commands']);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getGuard(): ?string
    {
        return $this->guard;
    }

    public function setGuard(?string $guard): void
    {
        $this->guard = $guard;
    }

    public function getFromPlaces(): array
    {
        return $this->fromPlaces;
    }

    public function setFromPlaces(array $fromPlaces): void
    {
        $this->fromPlaces = [];

        foreach ($fromPlaces as $fromPlace) {
            $this->addFromPlace($fromPlace);
        }
    }

    public function addFromPlace(int $fromPlace): void
    {
        $this->fromPlaces[] = $fromPlace;
    }

    public function getToPlaces(): array
    {
        return $this->toPlaces;
    }

    public function setToPlaces(array $toPlaces): void
    {
        $this->toPlaces = [];

        foreach ($toPlaces as $toPlace) {
            $this->addToPlace($toPlace);
        }
    }

    public function addToPlace(int $toPlace): void
    {
        $this->toPlaces[] = $toPlace;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'guard' => $this->guard,
            'fromPlaces' => $this->fromPlaces,
            'toPlaces' => $this->toPlaces,
            'commands' => array_map(fn (CommandInterface $command) => $command->toArray(), $this->commands),
        ];
    }
}
