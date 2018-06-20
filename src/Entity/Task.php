<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Task
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
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Model
     */
    private $model;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Generator
     */
    private $generator;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\StopCondition
     */
    private $stopCondition;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Json
     */
    private $stopConditionArguments;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Reducer
     */
    private $reducer;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @MbtAssert\Reporter
     */
    private $reporter;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    private $progress;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice({"not-started", "in-progress", "completed"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="ReproducePath", mappedBy="task", cascade={"persist"})
     */
    private $reproducePaths;

    public function __construct()
    {
        $this->reproducePaths = new ArrayCollection();
    }

    public function addReproducePath(ReproducePath $reproducePath): void
    {
        $reproducePath->setTask($this);
        $this->reproducePaths->add($reproducePath);
    }

    public function removeReproducePath(ReproducePath $reproducePath): void
    {
        $reproducePath->setTask(null);
        $this->reproducePaths->removeElement($reproducePath);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model)
    {
        $this->model = $model;
    }

    public function getGenerator(): string
    {
        return $this->generator;
    }

    public function setGenerator(string $generator)
    {
        $this->generator = $generator;
    }

    public function getStopCondition(): string
    {
        return $this->stopCondition;
    }

    public function setStopCondition(string $stopCondition)
    {
        $this->stopCondition = $stopCondition;
    }

    public function getStopConditionArguments()
    {
        return $this->stopConditionArguments;
    }

    public function setStopConditionArguments($stopConditionArguments)
    {
        $this->stopConditionArguments = $stopConditionArguments;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }

    public function setReducer($reducer)
    {
        $this->reducer = $reducer;
    }

    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress)
    {
        $this->progress = $progress;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return Collection|ReproducePath[]
     */
    public function getReproducePaths()
    {
        return $this->reproducePaths;
    }
}
