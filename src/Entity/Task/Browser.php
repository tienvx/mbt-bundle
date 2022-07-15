<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Task;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Task\Browser as BrowserModel;

#[Embeddable]
class Browser extends BrowserModel
{
    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    protected string $name = '';

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    protected string $version = '';
}
