<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\GeneratorOptions as GeneratorOptionsModel;

/**
 * @ORM\Embeddable
 */
class GeneratorOptions extends GeneratorOptionsModel
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    protected $transitionCoverage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    protected $placeCoverage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Positive
     */
    protected $maxSteps;
}
