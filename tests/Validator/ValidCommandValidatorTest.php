<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandManager;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator
 *
 * @uses \Tienvx\Bundle\MbtBundle\Command\CommandManager
 * @uses \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMousePointCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Mouse\ClickAtCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Mouse\ClickCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Window\OpenCommand
 * @uses \Tienvx\Bundle\MbtBundle\Command\Mouse\DragAndDropToObjectCommand
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class ValidCommandValidatorTest extends ConstraintValidatorTestCase
{
    protected HttpClientInterface|MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new ValidCommandValidator(new CommandManager($this->httpClient));
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
        $command->setCommand('clickAt');
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
            'invalidCommandMessage' => 'invalid command',
        ]);

        $command = new Command();
        $command->setCommand('notValidCommand');
        $this->validator->validate($command, $constraint);

        $this->buildViolation('invalid command')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.command')
            ->assertRaised();
    }

    public function testRequiredTarget()
    {
        $constraint = new ValidCommand([
            'targetRequiredMessage' => 'required target',
            'targetInvalidMessage' => 'invalid target',
        ]);

        $command = new Command();
        $command->setCommand('click');
        $command->setTarget(null);
        $this->validator->validate($command, $constraint);

        $this->buildViolation('required target')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.target')
            ->assertRaised();
    }

    public function testRequiredValue()
    {
        $constraint = new ValidCommand([
            'valueRequiredMessage' => 'required value',
        ]);

        $command = new Command();
        $command->setCommand('clickAt');
        $command->setTarget('css=.button');
        $command->setValue(null);
        $this->validator->validate($command, $constraint);

        $this->buildViolation('required value')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.value')
            ->assertRaised();
    }

    public function testInvalidTarget()
    {
        $constraint = new ValidCommand([
            'targetInvalidMessage' => 'invalid target',
        ]);

        $command = new Command();
        $command->setCommand('open');
        $command->setTarget('testing');
        $this->validator->validate($command, $constraint);

        $this->buildViolation('invalid target')
            ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
            ->atPath('property.path.target')
            ->assertRaised();
    }

    public function testInvalidValue()
    {
        $constraint = new ValidCommand([
            'valueInvalidMessage' => 'invalid value',
        ]);

        $command = new Command();
        $command->setCommand('dragAndDropToObject');
        $command->setTarget('css=.from');
        $command->setValue('.to');
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
