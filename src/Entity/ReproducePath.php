<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 */
class ReproducePath
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Model
     */
    private $model;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $steps;

    /**
     * @ORM\Version @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $length;

    /**
     * @ORM\Column(type="array")
     * @MbtAssert\Unique
     */
    private $messageHashes;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    private $task;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $bugMessage;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Reducer
     */
    private $reducer;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $distance;

    public function getId()
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model)
    {
        $this->model = $model;
    }

    public function getSteps(): string
    {
        return $this->steps;
    }

    public function setSteps(string $steps)
    {
        $this->steps = $steps;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length)
    {
        $this->length = $length;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version)
    {
        $this->version = $version;
    }

    public function getMessageHashes(): array
    {
        return $this->messageHashes;
    }

    public function setMessageHashes(array $messageHashes)
    {
        $this->messageHashes = $messageHashes;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    public function getBugMessage(): string
    {
        return $this->bugMessage;
    }

    public function setBugMessage(string $bugMessage)
    {
        $this->bugMessage = $bugMessage;
    }

    public function setReducer(string $reducer)
    {
        $this->reducer = $reducer;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function setDistance(int $distance)
    {
        $this->distance = $distance;
    }
}
