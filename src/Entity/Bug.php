<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Bug
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="bugs")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    private $task;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $message;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $steps;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice({"unverified", "valid", "invalid"})
     */
    private $status;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Reporter
     */
    private $reporter;

    public function getId()
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getSteps(): string
    {
        return $this->steps;
    }

    public function setSteps(string $steps)
    {
        $this->steps = $steps;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getReporter()
    {
        return $this->reporter;
    }

    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
    }
}
