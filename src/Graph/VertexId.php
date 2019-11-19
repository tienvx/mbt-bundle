<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

class VertexId
{
    /**
     * @var string
     */
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromPlaces(array $places): string
    {
        if (count($places) > 1) {
            sort($places);
        }

        $id = json_encode($places);

        return new static($id);
    }

    public function __toString()
    {
        return $this->id;
    }
}
