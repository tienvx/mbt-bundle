<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Tienvx\Bundle\MbtBundle\Annotation\Generator;
use Doctrine\Common\Annotations\Reader;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;

class GeneratorDiscovery
{

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var array
     */
    private $generators = [];

    /**
     * @var AdapterInterface
     */
    private $cache;


    /**
     * WorkerDiscovery constructor.
     *
     * @param Reader $annotationReader
     * @param AdapterInterface $cache
     */
    public function __construct(Reader $annotationReader, AdapterInterface $cache)
    {
        $this->annotationReader = $annotationReader;
        $this->cache = $cache;
    }

    /**
     * Returns all the workers
     */
    public function getGenerators() {
        if (!$this->generators) {
            $this->discoverGenerators();
        }

        return $this->generators;
    }

    /**
     * Discovers generators
     */
    private function discoverGenerators() {
        $generators = $this->cache->getItem('mbt.generators');
        if ($generators->isHit()) {
            $this->generators = $generators->get();
        }
        else {
            foreach (get_declared_classes() as $class) {
                if ($class instanceof GeneratorInterface) {
                    $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'Tienvx\Bundle\MbtBundle\Annotation\Generator');
                    if (!$annotation) {
                        continue;
                    }

                    /** @var Generator $annotation */
                    $this->generators[$annotation->getName()] = [
                        'class' => $class,
                        'annotation' => $annotation,
                    ];
                }
            }
            $generators->set($this->generators);
            $this->cache->save($generators);
        }
    }
}
