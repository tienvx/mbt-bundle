<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;

class GeneratorManager
{
    /**
     * @var GeneratorDiscovery
     */
    private $discovery;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var GraphBuilder
     */
    private $graphBuilder;


    public function __construct(GeneratorDiscovery $discovery, DataProvider $dataProvider, GraphBuilder $graphBuilder)
    {
        $this->discovery = $discovery;
        $this->dataProvider = $dataProvider;
        $this->graphBuilder = $graphBuilder;
    }

    /**
     * Returns a list of available workers.
     *
     * @return array
     */
    public function getGenerators(): array
    {
        return $this->discovery->getGenerators();
    }

    /**
     * Returns one generator by name
     *
     * @param $name
     * @return array
     *
     * @throws \Exception
     */
    public function getGenerator($name): array
    {
        $generators = $this->discovery->getGenerators();
        if (isset($generators[$name])) {
            return $generators[$name];
        }

        throw new \Exception('Generator not found.');
    }

    /**
     * Check if there is a generator by name
     *
     * @param $name
     * @return bool
     */
    public function hasGenerator($name): bool
    {
        $generators = $this->discovery->getGenerators();
        return isset($generators[$name]);
    }

    /**
     * Creates a worker
     *
     * @param $name
     * @return GeneratorInterface
     *
     * @throws \Exception
     */
    public function create($name)
    {
        $generators = $this->discovery->getGenerators();
        if (array_key_exists($name, $generators)) {
            $class = $generators[$name]['class'];
            if (!class_exists($class)) {
                throw new \Exception('Generator class does not exist.');
            }
            return new $class($this->dataProvider, $this->graphBuilder);
        }

        throw new \Exception('Generator does not exist.');
    }
}
