<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Exception;
use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Tienvx\Bundle\MbtBundle\Annotation\DataProvider;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class WorkflowSubscriber implements EventSubscriberInterface
{
    /** @var Reader $reader */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function onEntered(Event $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof Subject) {
            $subject->enterPlace($event->getMarking()->getPlaces());
        }
    }

    public function onTransition(Event $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof Subject) {
            $subject->applyTransition($event->getTransition()->getName());
        }
    }

    /**
     * @param GuardEvent $event
     * @throws Exception
     */
    public function onGuard(GuardEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof Subject) {
            if ($subject->needData()) {
                $transitionName = $event->getTransition()->getName();
                $reflectionMethod = new ReflectionMethod(get_class($subject), $transitionName);
                $dataProvider = $this->reader->getMethodAnnotation($reflectionMethod, DataProvider::class);
                if ($dataProvider && $dataProvider instanceof DataProvider) {
                    $data = call_user_func([$subject, $dataProvider->method]);
                    if (!is_array($data)) {
                        return;
                    }
                } else {
                    $data = [];
                }
                $subject->setData($data);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.guard' => 'onGuard',
            'workflow.transition' => 'onTransition',
            'workflow.entered' => 'onEntered',
        ];
    }
}
