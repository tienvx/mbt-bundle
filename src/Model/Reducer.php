<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Reducer implements ReducerInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ReducerInterface
    {
        $this->name = $name;

        return $this;
    }
}
