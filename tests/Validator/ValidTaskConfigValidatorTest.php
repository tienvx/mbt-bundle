<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfigInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfig;
use Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfigValidator;
use Tienvx\Bundle\MbtBundle\Entity\Task\TaskConfig;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfigValidator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class ValidTaskConfigValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var GeneratorManager|MockObject
     */
    protected GeneratorManager $generatorManager;

    /**
     * @var ReducerManager|MockObject
     */
    protected ReducerManager $reducerManager;

    /**
     * @var ChannelManager|MockObject
     */
    protected ChannelManager $channelManager;

    protected function createValidator()
    {
        $this->generatorManager = $this->createMock(GeneratorManager::class);
        $this->reducerManager = $this->createMock(ReducerManager::class);
        $this->channelManager = $this->createMock(ChannelManager::class);
        return new ValidTaskConfigValidator($this->generatorManager, $this->reducerManager, $this->channelManager);
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        if ($value) {
            $this->generatorManager->expects($this->once())->method('has')->with('random')->willReturn(true);
            $generator = $this->createMock(GeneratorInterface::class);
            $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
            $this->reducerManager->expects($this->once())->method('has')->with('random')->willReturn(true);
            $this->channelManager->expects($this->once())->method('all')->willReturn(['chat/slack']);
            $generator->expects($this->once())->method('validate')->with([
                'max_place_coverage' => 12.3,
                'max_transition_coverage' => 23.4,
            ])->willReturn(true);
        }
        $this->validator->validate($value, new ValidTaskConfig());

        $this->assertNoViolation();
    }

    public function getValidValues(): array
    {
        $validTaskConfig = new TaskConfig();
        $validTaskConfig->setGenerator('random');
        $validTaskConfig->setReducer('random');
        $validTaskConfig->setNotifyChannels(['chat/slack']);
        $validTaskConfig->setGeneratorConfig([
            'max_place_coverage' => 12.3,
            'max_transition_coverage' => 23.4,
        ]);
        return [
            [null],
            [$validTaskConfig],
        ];
    }

    public function testInvalidGenerator()
    {
        $this->generatorManager->expects($this->once())->method('has')->with('invalid')->willReturn(false);
        $this->generatorManager->expects($this->never())->method('get')->with('invalid');
        $constraint = new ValidTaskConfig([
            'message' => 'myMessage',
        ]);
        $taskConfig = new TaskConfig();
        $taskConfig->setGenerator('invalid');

        $this->validator->validate($taskConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidTaskConfig::IS_TASK_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testInvalidReducer()
    {
        $this->generatorManager->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->generatorManager->expects($this->never())->method('get')->with('random');
        $this->reducerManager->expects($this->once())->method('has')->with('invalid')->willReturn(false);
        $constraint = new ValidTaskConfig([
            'message' => 'myMessage',
        ]);
        $taskConfig = new TaskConfig();
        $taskConfig->setGenerator('random');
        $taskConfig->setReducer('invalid');

        $this->validator->validate($taskConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidTaskConfig::IS_TASK_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testInvalidChannels()
    {
        $this->generatorManager->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->generatorManager->expects($this->never())->method('get')->with('random');
        $this->reducerManager->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->channelManager->expects($this->once())->method('all')->willReturn([]);
        $constraint = new ValidTaskConfig([
            'message' => 'myMessage',
        ]);
        $taskConfig = new TaskConfig();
        $taskConfig->setGenerator('random');
        $taskConfig->setReducer('random');
        $taskConfig->setNotifyChannels(['invalid']);

        $this->validator->validate($taskConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidTaskConfig::IS_TASK_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testInvalidGeneratorConfig()
    {
        $this->generatorManager->expects($this->once())->method('has')->with('random')->willReturn(true);
        $generator = $this->createMock(RandomGenerator::class);
        $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
        $this->reducerManager->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->channelManager->expects($this->once())->method('all')->willReturn(['chat/slack']);
        $constraint = new ValidTaskConfig([
            'message' => 'myMessage',
        ]);
        $taskConfig = new TaskConfig();
        $taskConfig->setGenerator('random');
        $taskConfig->setReducer('random');
        $taskConfig->setNotifyChannels(['chat/slack']);
        $taskConfig->setGeneratorConfig([]);

        $this->validator->validate($taskConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidTaskConfig::IS_TASK_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            ValidTaskConfig::class,
            Email::class
        ));
        $this->validator->validate('test@example.com', new Email());
    }

    public function testUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            TaskConfigInterface::class,
            'stdClass'
        ));
        $this->validator->validate(new \stdClass(), new ValidTaskConfig());
    }
}
