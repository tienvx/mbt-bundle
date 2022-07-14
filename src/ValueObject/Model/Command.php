<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command as CommandModel;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;

#[ValidCommand]
class Command extends CommandModel
{
    #[Assert\NotBlank]
    protected string $command;

    protected ?string $target = null;

    protected ?string $value = null;
}
