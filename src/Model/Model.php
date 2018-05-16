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
     * @param bool $generatingSteps
     * @return Subject
     * @throws \Exception
     */
    public function createSubject(bool $generatingSteps = false)
    {
        $subjectClass = $this->getDefinition()->getMetadataStore()->getWorkflowMetadata()['subject'];
        if (!is_subclass_of($subjectClass, Subject::class)) {
            throw new \Exception('subject in metadata of a model must be subclass of Tienvx\Bundle\MbtBundle\Model\Model');
        }
        /** @var Subject $subject */
        $subject = new $subjectClass($generatingSteps);
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

            $data = $this->provideData($subject, $path, $transitionName, $dataIndex);

            if (!parent::can($subject, $transitionName)) {
                return false;
            }

            $this->updatePath($edge, $path, $data, $dataIndex);

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

    /**
     * @param Subject $subject
     * @param Path $path
     * @param $transitionName
     * @param int $dataIndex
     * @return array
     * @throws \Exception
     */
    private function provideData(Subject $subject, Path $path, $transitionName, int $dataIndex = null): array
    {
        if (!is_null($dataIndex)) {
            $data = $path->getDataAt($dataIndex);
            if (is_null($data)) {
                $data = $subject->provideData($transitionName);
            } else {
                $subject->setData($data);
            }
        } else {
            $data = $subject->provideData($transitionName);
        }
        return $data;
    }

    /**
     * @param Directed $edge
     * @param Path $path
     * @param array $data
     * @param int $dataIndex
     */
    private function updatePath(Directed $edge, Path $path, array $data, int $dataIndex = null): void
    {
        if (!is_null($dataIndex)) {
            $path->setDataAt($dataIndex, $data);
        } else {
            $path->addEdge($edge);
            $path->addData($data);
        }
    }
}
