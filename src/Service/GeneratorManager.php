<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;

class GeneratorManager
{
    /**
     * @var GeneratorDiscovery
     */
    private $discovery;


    public function __construct(GeneratorDiscovery $discovery)
    {
        $this->discovery = $discovery;
    }

    /**
     * Returns a list of available workers.
     *
     * @return array
     */
    public function getGenerators() {
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
    public function getGenerator($name) {
        $generators = $this->discovery->getGenerators();
        if (isset($generators[$name])) {
            return $generators[$name];
        }

        throw new \Exception('Generator not found.');
    }

    /**
     * Creates a worker
     *
     * @param $name
     * @return GeneratorInterface
     *
     * @throws \Exception
     */
    public function create($name) {
        $generators = $this->discovery->getGenerators();
        if (array_key_exists($name, $generators)) {
            $class = $generators[$name]['class'];
            if (!class_exists($class)) {
                throw new \Exception('Generator class does not exist.');
            }
            return new $class();
        }

        throw new \Exception('Generator does not exist.');
    }
}
