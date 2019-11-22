<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Doctrine\Common\Annotations\Reader;
use ReflectionObject;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class SubjectHelper
{
    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function invokePlace(SubjectInterface $subject, string $place): void
    {
        $reflectionObject = new ReflectionObject($subject);

        foreach ($reflectionObject->getMethods() as $reflectionMethod) {
            $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Place::class);
            if ($annotation instanceof Place && $annotation->getName() === $place) {
                $reflectionMethod->invoke($subject);
                break;
            }
        }
    }

    public function invokeTransition(SubjectInterface $subject, string $transition, ?Data $data): void
    {
        $reflectionObject = new ReflectionObject($subject);

        foreach ($reflectionObject->getMethods() as $reflectionMethod) {
            $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Transition::class);
            if ($annotation instanceof Transition && $annotation->getName() === $transition && $data instanceof Data) {
                $reflectionMethod->invoke($subject, $data);
                break;
            }
        }
    }
}
