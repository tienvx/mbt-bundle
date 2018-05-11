<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class Model extends StateMachine
{
    /**
     * @var SingleStateMarkingStore $modelMarkingStore
     */
    protected $modelMarkingStore;

    public function __construct(Definition $definition, SingleStateMarkingStore $markingStore = null, EventDispatcherInterface $dispatcher = null, string $name = 'unnamed')
    {
        $this->modelMarkingStore = $markingStore ?: new SingleStateMarkingStore();
        parent::__construct($definition, $this->modelMarkingStore, $dispatcher, $name);
    }

    /**
     * @return Subject
     * @throws \Exception
     */
    public function createSubject()
    {
        $subjectClass = $this->getDefinition()->getMetadataStore()->getWorkflowMetadata()['subject'];
        if (!is_subclass_of($subjectClass, Subject::class)) {
            throw new \Exception('subject in metadata of a model must be subclass of Tienvx\Bundle\MbtBundle\Model\Model');
        }
        $subject = new $subjectClass();
        return $subject;
    }


    /**
     * Similar to Workflow::apply, but does not dispatch events.
     *
     * @param Subject $subject
     * @param Directed $edge
     * @param Path $path
     * @param int $dataIndex
     * @return bool
     * @throws \Exception
     */
    public function applyModel(Subject $subject, Directed $edge, Path $path, int $dataIndex = null): bool
    {
        $marking = $this->getMarking($subject);
        $transitionName = $edge->getAttribute('name');

        foreach ($this->getDefinition()->getTransitions() as $transition) {
            if ($transition->getName() !== $transitionName) {
                continue;
            }

            if (!is_null($dataIndex)) {
                $data = $path->getDataAt($dataIndex);
                if (is_null($data)) {
                    $data = $subject->provideData($transitionName);
                }
                else {
                    $subject->setData($data);
                }
            }
            else {
                $data = $subject->provideData($transitionName);
            }

            if (!parent::can($subject, $transitionName)) {
                return false;
            }

            if (!is_null($dataIndex)) {
                $path->setDataAt($dataIndex, $data);
            }
            else {
                $path->addEdge($edge);
                $path->addData($data);
            }

            // Leave places
            $places = $transition->getFroms();
            foreach ($places as $place) {
                $marking->unmark($place);
            }

            $subject->applyTransition($transition->getName());

            // Enter places
            $places = $transition->getTos();
            foreach ($places as $place) {
                $subject->enterPlace($place);
                $marking->mark($place);
            }

            $this->modelMarkingStore->setMarking($subject, $marking);
        }

        return true;
    }
}
