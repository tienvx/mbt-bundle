<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tienvx\Bundle\MbtBundle\Validator\Tags;
use Tienvx\Bundle\MbtBundle\Validator\TagsValidator;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\TagsValidator
 */
class TagsValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new TagsValidator();
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new Tags());

        $this->assertNoViolation();
    }

    public function getValidValues(): array
    {
        return [
            [null],
            [''],
            ['tag1'],
            ['tag1,tag2'],
            ['tag1,tag2,tag3'],
        ];
    }

    public function testBlankTagIsInvalid()
    {
        $constraint = new Tags([
            'message' => 'myMessage',
        ]);

        $this->validator->validate('tag1,,tag3', $constraint);

        $this->buildViolation('myMessage')
            ->setCode(Tags::IS_TAGS_INVALID_ERROR)
            ->assertRaised();
    }

    public function testDuplicatedTagIsInvalid()
    {
        $constraint = new Tags([
            'message' => 'myMessage',
        ]);

        $this->validator->validate('tag1,tag2,tag1', $constraint);

        $this->buildViolation('myMessage')
            ->setCode(Tags::IS_TAGS_INVALID_ERROR)
            ->assertRaised();
    }

    public function testUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf('Expected argument of type "%s", "%s" given', Tags::class, Email::class));
        $this->validator->validate('test@example.com', new Email());
    }

    public function testUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Expected argument of type "%s", "%s" given', 'string', 'stdClass'));
        $this->validator->validate(new \stdClass(), new Tags());
    }
}
