<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\Entity\StaticCase;

/**
 * @Annotation
 */
class StaticCaseIdValidator extends ConstraintValidator
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
        if (!$constraint instanceof StaticCaseId) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\StaticCaseId');
        }

        if (!is_int($value)) {
            throw new UnexpectedTypeException($value, 'int');
        }

        $staticCase = $this->entityManager->getRepository(StaticCase::class)->find($value);

        if (!$staticCase || !$staticCase instanceof StaticCase) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ number }}', $value)
                ->addViolation();
        }
    }
}
