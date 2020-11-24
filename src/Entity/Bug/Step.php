<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;

/**
 * @ORM\Entity
 */
class Step extends StepModel
{
    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     */
    protected array $places;

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected string $color;

    /**
     * @Assert\Type("integer")
     */
    protected ?int $transition = null;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        foreach ($this->places as $place => $tokens) {
            if (!is_int($place)) {
                $context->buildViolation('The key of array should be of type integer.')
                    ->addViolation();
            }
        }
    }
}
