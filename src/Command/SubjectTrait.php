<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

trait SubjectTrait
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @param string $model
     * @param bool   $testing
     *
     * @return SubjectInterface
     *
     * @throws Exception
     */
    protected function getSubject(string $model, bool $testing = false): SubjectInterface
    {
        $subject = $this->subjectManager->createSubject($model);
        $subject->setUp($testing);

        return $subject;
    }
}
