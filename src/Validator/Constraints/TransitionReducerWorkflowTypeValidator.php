<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Workflow\StateMachine;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Reducer\Transition\TransitionReducer;

/**
 * @Annotation
 */
class TransitionReducerWorkflowTypeValidator extends ConstraintValidator
{
    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(WorkflowHelper $workflowHelper)
    {
        $this->workflowHelper = $workflowHelper;
    }

    public function validate($task, Constraint $constraint): void
    {
        if (!$constraint instanceof TransitionReducerWorkflowType) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TransitionReducerWorkflowType');
        }

        if (!$task instanceof TaskInterface) {
            throw new UnexpectedValueException($task, TaskInterface::class);
        }

        if (!$this->workflowHelper->has($task->getWorkflow()->getName())) {
            return;
        }

        $this->validateValue($task, $constraint);
    }

    protected function validateValue(TaskInterface $task, Constraint $constraint): void
    {
        $workflow = $this->workflowHelper->get($task->getWorkflow()->getName());
        if ($task->getReducer()->getName() === TransitionReducer::getName() && $workflow instanceof StateMachine) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ workflow }}', $workflow->getName())
                ->setParameter('{{ type }}', 'state_machine')
                ->addViolation();
        }
    }
}
