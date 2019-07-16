<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Exception;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Tienvx\Bundle\MbtBundle\Annotation\DataProvider;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class WorkflowSubscriber implements EventSubscriberInterface
{
    /** @var Reader $reader */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function onEntered(EnteredEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof AbstractSubject) {
            $places = array_keys(array_filter($event->getMarking()->getPlaces()));
            $subject->enterPlace($places);
        }
    }

    public function onTransition(TransitionEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof AbstractSubject) {
            $subject->storeData();
            $subject->applyTransition($event->getTransition()->getName());
        }
    }

    /**
     * @param GuardEvent $event
     *
     * @throws Exception
     */
    public function onGuard(GuardEvent $event)
    {
        $subject = $event->getSubject();
        $transitionName = $event->getTransition()->getName();

        if ($subject instanceof AbstractSubject) {
            if ($subject->needData() && method_exists($subject, $transitionName)) {
                $reflectionMethod = new ReflectionMethod(get_class($subject), $transitionName);
                $dataProvider = $this->reader->getMethodAnnotation($reflectionMethod, DataProvider::class);
                if ($dataProvider && $dataProvider instanceof DataProvider) {
                    $data = call_user_func([$subject, $dataProvider->method]);
                    if (!($data instanceof StepData)) {
                        return;
                    }
                } else {
                    $data = new StepData();
                }
                $subject->setData($data);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            GuardEvent::class => 'onGuard',
            TransitionEvent::class => 'onTransition',
            EnteredEvent::class => 'onEntered',
        ];
    }
}
