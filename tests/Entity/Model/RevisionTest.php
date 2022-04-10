<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Model;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Validator\CustomConstraintValidatorFactory;
use Tienvx\Bundle\MbtBundle\Tests\Model\Model\RevisionTest as RevisionModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner
 * @uses \Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\Validator\TagsValidator
 * @uses \Tienvx\Bundle\MbtBundle\Validator\ValidCommand
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Model\Command
 */
class RevisionTest extends RevisionModelTest
{
    protected ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setConstraintValidatorFactory(new CustomConstraintValidatorFactory())
            ->getValidator();
    }

    public function testValidateInvalidRevision(): void
    {
        $violations = $this->validator->validate($this->revision);
        $this->assertCount(11, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[0].toPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1].fromPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions:
    mbt.model.missing_start_transition
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[1].target:
    mbt.model.command.required_target (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[1].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[1].commands[1].value:
    mbt.model.command.required_value (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1].toPlaces:
    mbt.model.missing_to_places (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    public function testValidateInvalidRevisionTooManyStartTransitions(): void
    {
        $this->revision->getTransition(0)->setFromPlaces([]);
        $this->revision->getTransition(1)->setFromPlaces([]);
        $violations = $this->validator->validate($this->revision);
        $this->assertCount(11, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[0].toPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1]:
    mbt.model.missing_places
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions:
    mbt.model.too_many_start_transitions
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[0].commands[1].target:
    mbt.model.command.required_target (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[1].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places[1].commands[1].value:
    mbt.model.command.required_value (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions[1].toPlaces:
    mbt.model.missing_to_places (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    public function testNoPlacesAndTransitions(): void
    {
        $violations = $this->validator->validate($this->createRevision());
        $this->assertCount(2, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).places:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
Object(Tienvx\Bundle\MbtBundle\Entity\Model\Revision).transitions:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    protected function createRevision(): RevisionInterface
    {
        return new Revision();
    }
}
