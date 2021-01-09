<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Command as CommandModel;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;

class Command extends CommandModel
{
    /**
     * @Assert\NotBlank
     * @ValidCommand
     */
    protected string $command;

    protected ?string $target = null;

    protected ?string $value = null;

    /**
     * @Assert\Callback
     */
    public function validateTarget(ExecutionContextInterface $context, $payload): void
    {
        $commandsRequireTarget = [
            AlertCommandRunner::ANSWER_PROMPT,
            ...AssertionRunner::ALL_COMMANDS,
            ...KeyboardCommandRunner::ALL_COMMANDS,
            ...MouseCommandRunner::ALL_COMMANDS,
            ...WaitCommandRunner::ALL_COMMANDS,
            WindowCommandRunner::OPEN,
            WindowCommandRunner::SET_WINDOW_SIZE,
            WindowCommandRunner::SELECT_WINDOW,
            WindowCommandRunner::SELECT_FRAME,
        ];
        if (in_array($this->command, $commandsRequireTarget) && is_null($this->target)) {
            $context->buildViolation(sprintf('Command %s need target', $this->command))
                ->atPath('target')
                ->addViolation();
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateValue(ExecutionContextInterface $context, $payload): void
    {
        $commandsRequireValue = [
            AssertionRunner::ASSERT_TEXT,
            AssertionRunner::ASSERT_NOT_TEXT,
            AssertionRunner::ASSERT_VALUE,
            AssertionRunner::ASSERT_SELECTED_VALUE,
            AssertionRunner::ASSERT_NOT_SELECTED_VALUE,
            AssertionRunner::ASSERT_SELECTED_LABEL,
            AssertionRunner::ASSERT_NOT_SELECTED_LABEL,
            MouseCommandRunner::ADD_SELECTION,
            MouseCommandRunner::REMOVE_SELECTION,
            MouseCommandRunner::CLICK_AT,
            MouseCommandRunner::DOUBLE_CLICK_AT,
            MouseCommandRunner::DRAG_AND_DROP_TO_OBJECT,
            MouseCommandRunner::MOUSE_DOWN_AT,
            MouseCommandRunner::MOUSE_MOVE_AT,
            MouseCommandRunner::MOUSE_UP_AT,
            MouseCommandRunner::SELECT,
            ...WaitCommandRunner::ALL_COMMANDS,
        ];
        if (in_array($this->command, $commandsRequireValue) && is_null($this->value)) {
            $context->buildViolation(sprintf('Command %s need value', $this->command))
                ->atPath('value')
                ->addViolation();
        }
    }
}
