<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Tienvx\Bundle\MbtBundle\Steps\Data;

class WorkflowSubscriber implements EventSubscriberInterface
{
    public const DATA_CONTEXT = 'data';

    /**
     * @var array
     */
    protected $places;

    /**
     * @var array
     */
    protected $transitions;

    public function __construct(array $places, array $transitions)
    {
        $this->places = $places;
        $this->transitions = $transitions;
    }

    public function onEntered(EnteredEvent $event): void
    {
        $subject = $event->getSubject();
        $places = array_keys(array_filter($event->getMarking()->getPlaces()));

        foreach ($places as $place) {
            $this->enterPlace($subject, $place);
        }
    }

    public function onTransition(TransitionEvent $event): void
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition()->getName();
        $data = $event->getContext()[self::DATA_CONTEXT] ?? null;

        $this->transit($subject, $transition, $data);
    }

    public static function getSubscribedEvents()
    {
        return [
            TransitionEvent::class => 'onTransition',
            EnteredEvent::class => 'onEntered',
        ];
    }

    protected function enterPlace(object $subject, string $place): void
    {
        $subjectClass = get_class($subject);
        if (isset($this->places[$subjectClass][$place])) {
            $method = $this->places[$subjectClass][$place];
            $callable = [$subject, $method];
            if (is_callable($callable)) {
                $callable();
            }
        }
    }

    protected function transit(object $subject, string $transition, $data): void
    {
        $subjectClass = get_class($subject);

        if (isset($this->transitions[$subjectClass][$transition])) {
            $method = $this->transitions[$subjectClass][$transition];
            $callable = [$subject, $method];
            if (is_callable($callable) && $data instanceof Data) {
                $callable($data);
            }
        }
    }
}
