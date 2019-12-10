<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Helper\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;

class SubjectManager
{
    /**
     * @var array
     */
    protected $subjects;

    /**
     * @var MarkingHelper
     */
    protected $markingHelper;

    /**
     * @var ModelHelper
     */
    protected $modelHelper;

    public function __construct(array $subjects, MarkingHelper $markingHelper, ModelHelper $modelHelper)
    {
        $this->subjects = $subjects;
        $this->markingHelper = $markingHelper;
        $this->modelHelper = $modelHelper;
    }

    /**
     * @throws Exception
     */
    public function create(string $model): SubjectInterface
    {
        $class = $this->subjects[$model] ?? null;
        if (!is_null($class)) {
            return new $class();
        }
        throw new Exception(sprintf('Subject for model %s not found', $model));
    }

    public function createAndSetUp(string $model, bool $testing = false): SubjectInterface
    {
        $subject = $this->create($model);
        $subject->setUp($testing);
        $this->markingHelper->init($this->modelHelper->get($model), $subject);

        return $subject;
    }
}
