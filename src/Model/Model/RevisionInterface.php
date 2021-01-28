<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface RevisionInterface
{
    public function getModel(): ?ModelInterface;

    public function setModel(?ModelInterface $model): void;

    /**
     * @return PlaceInterface[]
     */
    public function getPlaces(): array;

    public function setPlaces(PlaceInterface ...$places): void;

    public function addPlace(PlaceInterface $place): void;

    public function getPlace(int $index): ?PlaceInterface;

    /**
     * @return TransitionInterface[]
     */
    public function getTransitions(): array;

    public function setTransitions(TransitionInterface ...$transitions): void;

    public function addTransition(TransitionInterface $transition): void;

    public function getTransition(int $index): ?TransitionInterface;

    public function toArray(): array;
}
