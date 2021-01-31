<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision as BaseRevision;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

/**
 * @ORM\Entity
 */
class Revision extends BaseRevision
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Model", inversedBy="revisions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected ?ModelInterface $model = null;

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Place")
     * })
     * @Assert\Valid
     */
    protected array $places = [];

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition")
     * })
     * @Assert\Valid
     */
    protected array $transitions = [];

    /**
     * @Assert\Callback
     */
    public function validatePlacesInTransitions(ExecutionContextInterface $context, $payload): void
    {
        foreach ($this->transitions as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $fromPlaces = $transition->getFromPlaces();
                if ($fromPlaces && array_diff($fromPlaces, array_keys($this->places))) {
                    $context->buildViolation('mbt.model.places_invalid')
                        ->atPath(sprintf('transitions[%d].fromPlaces', $index))
                        ->addViolation();
                }
                $toPlaces = $transition->getToPlaces();
                if ($toPlaces && array_diff($toPlaces, array_keys($this->places))) {
                    $context->buildViolation('mbt.model.places_invalid')
                        ->atPath(sprintf('transitions[%d].toPlaces', $index))
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateStartTransitions(ExecutionContextInterface $context, $payload): void
    {
        $startTransitions = array_filter(
            $this->transitions,
            fn ($transition) => $transition instanceof TransitionInterface && 0 === count($transition->getFromPlaces())
        );
        if (0 === count($startTransitions)) {
            $context->buildViolation('mbt.model.missing_start_transition')
                ->atPath('transitions')
                ->addViolation();
        }
        if (count($startTransitions) > 1) {
            $context->buildViolation('mbt.model.too_many_start_transitions')
                ->atPath('transitions')
                ->addViolation();
        }
    }
}
