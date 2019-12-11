<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Generator;
use Symfony\Component\Workflow\Definition;
use Tienvx\Bundle\MbtBundle\Model\Model;

class ModelHelper
{
    /**
     * @var array
     */
    protected $models = [];

    public function addModel(string $name, string $type, Definition $definition): void
    {
        $this->models[$name] = [
            $definition,
            $name,
            $type,
        ];
    }

    public function has(string $model): bool
    {
        return isset($this->models[$model]);
    }

    /**
     * @throws Exception
     */
    public function get(string $model): Model
    {
        if (!$this->has($model)) {
            throw new Exception(sprintf('Model "%s" does not exist', $model));
        }

        return new Model(...$this->models[$model]);
    }

    /**
     * @throws Exception
     */
    public function getDefinition(string $model): Definition
    {
        if (!$this->has($model)) {
            throw new Exception(sprintf('Model "%s" does not exist', $model));
        }

        return $this->models[$model][0];
    }

    public function all(): Generator
    {
        foreach ($this->models as $args) {
            yield new Model(...$args);
        }
    }

    public function count(): int
    {
        return count($this->models);
    }

    public function checksum(string $model): string
    {
        $definition = $this->getDefinition($model);
        $transitions = [];
        foreach ($definition->getTransitions() as $transition) {
            $transitions[] = [
                0 => $transition->getName(),
                1 => $transition->getFroms(),
                2 => $transition->getTos(),
            ];
        }
        $content = [
            0 => $definition->getPlaces(),
            1 => $transitions,
            2 => $definition->getInitialPlaces(),
        ];

        return md5(json_encode($content));
    }
}
