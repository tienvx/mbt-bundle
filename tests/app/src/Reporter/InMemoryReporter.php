<?php

namespace App\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class InMemoryReporter implements ReporterInterface
{
    /**
     * @var array
     */
    protected $reports = [];

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    public function __construct(SubjectManager $subjectManager)
    {
        $this->subjectManager = $subjectManager;
    }

    public static function getName(): string
    {
        return 'in-memory';
    }

    public function getLabel(): string
    {
        return 'In Memory';
    }

    public static function support(): bool
    {
        return true;
    }

    /**
     * @param Bug $bug
     *
     * @throws Exception
     */
    public function report(Bug $bug)
    {
        $task = $bug->getTask();
        if (!$task instanceof Task) {
            return;
        }

        $model = $task->getModel()->getName();
        $subject = $this->subjectManager->createSubject($model);
        $this->reports[$bug->getId()] = [
            'status' => true,
            'screenshot' => $subject->getScreenshotUrl($bug->getId(), 0),
        ];
    }

    public function isReported($id)
    {
        return !empty($this->reports[$id]['status']);
    }

    public function reset()
    {
        $this->reports = [];
    }

    public function hasScreenshot($id)
    {
        return !empty($this->reports[$id]['screenshot']);
    }
}
