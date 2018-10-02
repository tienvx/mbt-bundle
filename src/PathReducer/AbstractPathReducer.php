<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractPathReducer implements PathReducerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager
    ) {
        $this->dispatcher       = $dispatcher;
        $this->subjectManager   = $subjectManager;
        $this->entityManager    = $entityManager;
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function finish(int $bugId)
    {
        $event = new ReducerFinishEvent($bugId);

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);
    }

    /**
     * @param Bug $bug
     * @param Path $path
     * @throws Exception
     */
    protected function updatePath(Bug $bug, Path $path)
    {
        $bug->setPath(serialize($path));
        $bug->setLength($path->countPlaces());
        $this->entityManager->flush();
    }

    public function handle(string $message)
    {
    }

    /**
     * @param Bug $bug
     * @throws Exception
     */
    public function reduce(Bug $bug)
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not reduce the bug: No workflows were defined');
        }
    }
}
