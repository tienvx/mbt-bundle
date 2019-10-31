<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Workflow\Registry;

trait WorkflowRegisterTrait
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }
}
