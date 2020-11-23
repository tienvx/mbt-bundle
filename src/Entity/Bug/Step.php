<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

/**
 * @ORM\Entity
 */
class Step extends StepModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Bug", inversedBy="steps")
     */
    protected BugInterface $bug;

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     */
    protected array $places;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected string $color;

    /**
     * @ORM\Column(type="integer", nullable=true)
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
