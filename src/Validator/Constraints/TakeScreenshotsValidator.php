<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class TakeScreenshotsValidator extends ConstraintValidator
{
    /**
     * @var FilesystemInterface
     */
    private $mbtStorage;

    public function setMbtStorage(FilesystemInterface $mbtStorage): void
    {
        $this->mbtStorage = $mbtStorage;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TakeScreenshots) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TakeScreenshots');
        }

        if (null === $value) {
            return;
        }

        if (true === $value && !$this->mbtStorage instanceof FilesystemInterface) {
            $this->context->buildViolation($constraint->getMessage())
                ->addViolation();
        }
    }
}
