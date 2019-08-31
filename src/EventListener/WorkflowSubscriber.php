<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class WorkflowSubscriber implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function onEntered(EnteredEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof SubjectInterface) {
            $places = array_keys(array_filter($event->getMarking()->getPlaces()));
            foreach ($places as $place) {
                $reflectionObject = new ReflectionObject($subject);

                foreach ($reflectionObject->getMethods() as $reflectionMethod) {
                    $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Place::class);
                    if ($annotation instanceof Place && $annotation->getName() === $place) {
                        $reflectionMethod->invoke($subject);
                    }
                }
            }
        }
    }

    public function onTransition(TransitionEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof SubjectInterface) {
            $reflectionObject = new ReflectionObject($subject);

            foreach ($reflectionObject->getMethods() as $reflectionMethod) {
                $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Transition::class);
                if ($annotation instanceof Transition && $annotation->getName() === $event->getTransition()->getName()) {
                    $context = $event->getContext();
                    if (isset($context['data']) && $context['data'] instanceof Data) {
                        $reflectionMethod->invoke($subject, $context['data']);
                    }
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransitionEvent::class => 'onTransition',
            EnteredEvent::class => 'onEntered',
        ];
    }
}
