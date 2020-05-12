<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Model\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Model\WorkflowInterface;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @MbtAssert\TransitionReducerWorkflowType
 */
class Task extends TaskModel
{
    use TimestampableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @MbtAssert\Workflow
     */
    protected $workflow;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @MbtAssert\Generator
     */
    protected $generator;

    /**
     * @ORM\Embedded(class="GeneratorOptions")
     * @Assert\Valid
     */
    protected $generatorOptions;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @MbtAssert\Reducer
     */
    protected $reducer;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    protected $reporters;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @MbtAssert\TaskStatus
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Bug", mappedBy="task", cascade={"persist"})
     */
    protected $bugs;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    protected $takeScreenshots;

    public function __construct()
    {
        parent::__construct();
        $this->generatorOptions = new GeneratorOptions();
    }

    /**
     * @MbtAssert\Reporters
     */
    public function getReporters(): array
    {
        $values = [];
        foreach (json_decode($this->reporters, true) as $reporter) {
            $values[] = new Reporter($reporter);
        }

        return $values;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow($this->workflow);
    }

    public function getGenerator(): GeneratorInterface
    {
        return new Generator($this->generator);
    }

    public function getReducer(): ReducerInterface
    {
        return new Reducer($this->reducer);
    }
}
