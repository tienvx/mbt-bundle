<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfigInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ValidTaskConfigValidator extends ConstraintValidator
{
    protected GeneratorManager $generatorManager;
    protected ReducerManager $reducerManager;
    protected ChannelManager $channelManager;

    public function __construct(
        GeneratorManager $generatorManager,
        ReducerManager $reducerManager,
        ChannelManager $channelManager
    ) {
        $this->generatorManager = $generatorManager;
        $this->reducerManager = $reducerManager;
        $this->channelManager = $channelManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidTaskConfig) {
            throw new UnexpectedTypeException($constraint, ValidTaskConfig::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof TaskConfigInterface) {
            throw new UnexpectedValueException($value, TaskConfigInterface::class);
        }

        $valid = false;
        if (
            $this->generatorManager->has($value->getGenerator())
            && $this->reducerManager->has($value->getReducer())
            && !array_diff($value->getNotifyChannels(), $this->channelManager->all())
        ) {
            $generator = $this->generatorManager->get($value->getGenerator());
            $valid = $generator->validate($value->getGeneratorConfig());
        }
        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setCode(ValidTaskConfig::IS_TASK_CONFIG_INVALID_ERROR)
                ->addViolation();
        }
    }
}
