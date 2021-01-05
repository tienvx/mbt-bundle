<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManager
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AlertCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WaitCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner
 */
class ValidCommandValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidCommandValidator(new CommandRunnerManager([
            new AlertCommandRunner(),
            new AssertionRunner(),
            new KeyboardCommandRunner(),
            new MouseCommandRunner(),
            new WaitCommandRunner(),
            new WindowCommandRunner(),
        ]));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new ValidCommand());

        $this->assertNoViolation();
    }

    public function getValidValues(): array
    {
        return [
            [null],
            [''],
            ['click'],
            ['assertTitle'],
        ];
    }

    public function testInvalidCommand()
    {
        $constraint = new ValidCommand([
            'message' => 'myMessage',
        ]);

        $this->validator->validate('notValidCommand', $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
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
        $this->expectExceptionMessage(sprintf('Expected argument of type "%s", "%s" given', 'string', 'stdClass'));
        $this->validator->validate(new \stdClass(), new ValidCommand());
    }
}
