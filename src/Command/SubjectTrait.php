<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

trait SubjectTrait
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @param string $model
     *
     * @return AbstractSubject
     *
     * @throws Exception
     */
    protected function getSubject(string $model): AbstractSubject
    {
        $subject = $this->subjectManager->createSubject($model);
        $subject->setUp();

        return $subject;
    }
}
