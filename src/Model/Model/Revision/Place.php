<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model\Revision;

use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\HasCommands;

class Place implements PlaceInterface
{
    use HasCommands;

    protected string $label = '';

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data)
    {
        $this->label = $data['label'];
        $this->commands = array_map(
            fn (array $command) => CommandFactory::createFromArray($command),
            $data['commands']
        );
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'commands' => array_map(fn (CommandInterface $command) => $command->toArray(), $this->commands),
        ];
    }
}
