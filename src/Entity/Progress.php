<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
}
