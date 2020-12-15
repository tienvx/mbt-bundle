<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Task;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig as SeleniumConfigModel;
use Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfig;

/**
 * @ORM\Embeddable
 * @ValidSeleniumConfig
 */
class SeleniumConfig extends SeleniumConfigModel
{
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $provider = '';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $platform = '';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $browser = '';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $browserVersion = '';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $resolution = '';
}
