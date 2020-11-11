<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Selenium;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Selenium\Command as CommandModel;

/**
 * @ORM\Entity
 */
class Command extends CommandModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $command;

    /**
     * @ORM\Column(type="string")
     */
    protected string $target;

    /**
     * @ORM\Column(type="string")
     */
    protected string $value;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place", inversedBy="assertions")
     */
    protected PlaceInterface $place;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition", inversedBy="actions")
     */
    protected TransitionInterface $transition;
}
