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
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $steps;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $length;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $totalMessages;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $handledMessages;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    private $task;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $bugMessage;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Reducer
     */
    private $reducer;

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

    public function getTotalMessages(): int
    {
        return $this->totalMessages;
    }

    public function setTotalMessages(int $totalMessages)
    {
        $this->totalMessages = $totalMessages;
    }

    public function getHandledMessages(): int
    {
        return $this->handledMessages;
    }

    public function setHandledMessages(int $handledMessages)
    {
        $this->handledMessages = $handledMessages;
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
}
