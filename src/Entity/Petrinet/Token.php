<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Token as BaseToken;

/**
 * @ORM\Entity
 * @ORM\Table(name="token")
 */
class Token extends BaseToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
}
