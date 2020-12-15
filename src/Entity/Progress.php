<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\Model\Progress as ProgressModel;

/**
 * @ORM\Embeddable
 */
class Progress extends ProgressModel
{
    /**
     * @ORM\Column(type="integer", options={"default": 0})
     * @Assert\Positive
     */
    protected int $total = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     * @Assert\Positive
     */
    protected int $processed = 0;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->processed > $this->total) {
            $context->buildViolation('Processed should be less than or equal to total.')
                ->atPath('processed')
                ->addViolation();
        }
    }
}
