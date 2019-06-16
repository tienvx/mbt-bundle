<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

/**
 * @Annotation
 */
class BugIdValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BugId) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\BugId');
        }

        if (!is_int($value)) {
            throw new UnexpectedTypeException($value, 'int');
        }

        $bug = $this->entityManager->getRepository(Bug::class)->find($value);

        if (!$bug || !$bug instanceof Bug) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ number }}', $value)
                ->addViolation();
        }
    }
}
