<?php

namespace App\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
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
     * @throws Exception
     */
    public function report(Bug $bug): void
    {
        $model = $bug->getModel()->getName();
        $subject = $this->subjectManager->create($model);
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
