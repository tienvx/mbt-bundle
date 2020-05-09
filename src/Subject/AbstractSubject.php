<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Tienvx\Bundle\MbtBundle\Model\Subject\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Subject\ScreenshotInterface;
use Tienvx\Bundle\MbtBundle\Model\Subject\SetUpInterface;
use Tienvx\Bundle\MbtBundle\Model\Subject\TearDownInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

abstract class AbstractSubject implements SubjectInterface, ScreenshotInterface, SetUpInterface, TearDownInterface, MarkingInterface
{
    use ScreenshotTrait;
    use MarkingTrait;
    use SetUpTearDownTrait;
}
