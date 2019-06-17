<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

class Path
{
    /**
     * @var array
     */
    protected $steps;

    /**
     * @return array
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @param array $steps
     */
    public function setSteps(array $steps): void
    {
        $this->steps = $steps;
    }

    /**
     * Path constructor.
     *
     * @param array $steps
     */
    public function __construct(array $steps = [])
    {
        $this->steps = $steps;
    }

    public function addStep(array $step)
    {
        $this->steps[] = $step;
    }

    public function countTransitions(): int
    {
        return count($this->steps) - 1;
    }

    public function countPlaces(): int
    {
        return count($this->steps);
    }

    public function countUniqueTransitions(): int
    {
        return count(array_unique(array_filter(array_column($this->steps, 0))));
    }

    public function countUniquePlaces(): int
    {
        return count(array_unique(call_user_func_array('array_merge', array_column($this->steps, 2))));
    }

    public function getTransitionAt(int $index): ?string
    {
        return $this->steps[$index][0];
    }

    public function getDataAt(int $index): ?array
    {
        return $this->steps[$index][1];
    }

    public function getPlacesAt(int $index): array
    {
        return $this->steps[$index][2];
    }

    public function setTransitionAt(int $index, array $transition)
    {
        $this->steps[$index][0] = $transition;
    }

    public function setDataAt(int $index, array $data)
    {
        $this->steps[$index][1] = $data;
    }

    public function setPlacesAt(int $index, array $places)
    {
        $this->steps[$index][2] = $places;
    }

    /**
     * @param Path $path
     *
     * @return array
     */
    public static function normalize(Path $path): array
    {
        return $path->getSteps();
    }

    /**
     * @param Path $path
     *
     * @return string
     */
    public static function serialize(Path $path): string
    {
        return json_encode(self::normalize($path));
    }

    /**
     * @param array $steps
     *
     * @return Path
     */
    public static function denormalize(array $steps): Path
    {
        return new Path($steps);
    }

    /**
     * @param string $steps
     *
     * @return Path
     */
    public static function deserialize(string $steps): Path
    {
        return self::denormalize(json_decode($steps, true));
    }
}
