<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class ValidCommandValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidCommandValidator(new CommandRunnerManager(
            [
                new AlertCommandRunner(),
                new AssertionRunner(),
                new KeyboardCommandRunner(),
                new MouseCommandRunner(),
                new ScriptCommandRunner(),
                new StoreCommandRunner(),
                new WaitCommandRunner(),
                new WindowCommandRunner(),
            ],
            new CommandPreprocessor()
        ));
    }

    /**
     * @dataProvider validCommandsProvider
     */
    public function testValidCommand($command)
    {
        $this->validator->validate($command, new ValidCommand());

        $this->assertNoViolation();
    }

    public function validCommandsProvider(): array
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK_AT);
        $command->setTarget('css=.button');
        $command->setValue('123,234');

        return [
            [null],
            [$command],
        ];
    }

    public function testInvalidCommand()
    {
        $constraint = new ValidCommand([
            'commandMessage' => 'invalid command',
        ]);

        $command = new Command();
        $command->setCommand('notValidCommand');
        $this->validator->validate($command, $constraint);

        $this->buildViolation('invalid command')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.command')
            ->assertRaised();
    }

    public function testInvalidTarget()
    {
        $constraint = new ValidCommand([
            'targetMessage' => 'invalid target',
        ]);

        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK);
        $command->setTarget(null);
        $this->validator->validate($command, $constraint);

        $this->buildViolation('invalid target')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.target')
            ->assertRaised();
    }

    public function testInvalidValue()
    {
        $constraint = new ValidCommand([
            'valueMessage' => 'invalid value',
        ]);

        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK_AT);
        $command->setTarget('css=.button');
        $command->setValue(null);
        $this->validator->validate($command, $constraint);

        $this->buildViolation('invalid value')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.value')
            ->assertRaised();
    }

    public function testUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf('Expected argument of type "%s", "%s" given', ValidCommand::class, Email::class)
        );
        $this->validator->validate('assertAlert', new Email());
    }

    public function testUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            sprintf('Expected argument of type "%s", "%s" given', Command::class, 'stdClass')
        );
        $this->validator->validate(new \stdClass(), new ValidCommand());
    }
}
