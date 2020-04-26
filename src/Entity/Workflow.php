<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Workflow as WorkflowModel;

/**
 * @ORM\Embeddable
 */
class Workflow extends WorkflowModel
{
    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected $name;
}
