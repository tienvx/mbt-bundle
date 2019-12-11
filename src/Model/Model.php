<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Exception;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlockerList;
use Symfony\Component\Workflow\WorkflowInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class Model implements WorkflowInterface
{
    protected $definition;
    protected $markingStore;
    protected $name;
    protected $type;

    public function __construct(Definition $definition, string $name, string $type)
    {
        $this->definition = $definition;
        $this->markingStore = new MethodMarkingStore('state_machine' === $type);
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarking(object $subject)
    {
        $marking = $this->getMarkingStore()->getMarking($subject);
        if (!$marking->getPlaces()) {
            throw new Exception('Marking is not initialized');
        }

        return $marking;
    }

    public function apply(object $subject, string $transitionName, array $context = []): Marking
    {
        if (!$subject instanceof SubjectInterface) {
            throw new Exception(sprintf('Subject of model %s must implement interface %s', $this->name, SubjectInterface::class));
        }

        $marking = $this->getMarking($subject);

        foreach ($this->definition->getTransitions() as $transition) {
            if ($transition->getName() === $transitionName) {
                $this->leave($transition, $marking);

                $this->enter($transition, $marking);

                $this->markingStore->setMarking($subject, $marking, $context);

                break;
            }
        }

        return $marking;
    }

    public function buildTransitionBlockerList(object $subject, string $transitionName): TransitionBlockerList
    {
        return new TransitionBlockerList();
    }

    public function can(object $subject, string $transitionName): bool
    {
        return false;
    }

    public function getDefinition(): Definition
    {
        return $this->definition;
    }

    public function getEnabledTransitions(object $subject): array
    {
        return [];
    }

    public function getMarkingStore(): MarkingStoreInterface
    {
        return $this->markingStore;
    }

    public function getMetadataStore(): MetadataStoreInterface
    {
        return $this->definition->getMetadataStore();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function leave(Transition $transition, Marking $marking): void
    {
        $places = $transition->getFroms();

        foreach ($places as $place) {
            $marking->unmark($place);
        }
    }

    protected function enter(Transition $transition, Marking $marking): void
    {
        $places = $transition->getTos();

        foreach ($places as $place) {
            $marking->mark($place);
        }
    }
}
