<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Fhaculty\Graph\Edge\Directed;
use Tienvx\Bundle\MbtBundle\Graph\Path;

abstract class Subject
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var boolean
     */
    protected $callSUT;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var boolean
     */
    protected $recordPath;

    /**
     * @var Path
     */
    protected $recordedPath;

    /**
     * @var Directed
     */
    protected $currentEdge;

    /**
     * @var bool
     */
    protected $firstVertexAdded;

    public function __construct()
    {
        $this->callSUT = false;
        $this->recordPath = false;
        $this->firstVertexAdded = false;
    }

    /**
     * @param $callSUT boolean
     */
    public function setCallSUT(bool $callSUT)
    {
        $this->callSUT = $callSUT;
    }

    /**
     * @param $recordPath boolean
     */
    public function setRecordPath(bool $recordPath)
    {
        $this->recordPath = $recordPath;
        if ($recordPath) {
            $this->recordedPath = new Path();
        }
    }

    /**
     * @return bool
     */
    public function isRecordingPath(): bool
    {
        return $this->recordPath;
    }

    /**
     * @return Path
     */
    public function getRecordedPath(): Path
    {
        return $this->recordedPath;
    }

    /**
     * @param $currentEdge Directed
     */
    public function setCurrentEdge(Directed $currentEdge)
    {
        $this->currentEdge = $currentEdge;
    }

    public function recordStep()
    {
        if ($this->recordedPath) {
            if (!$this->firstVertexAdded) {
                $this->recordedPath->addVertex($this->currentEdge->getVertexStart());
                $this->firstVertexAdded = true;
            }
            $this->recordedPath->addEdge($this->currentEdge);
            $this->recordedPath->addVertex($this->currentEdge->getVertexEnd());
        }
    }

    public function recordData(array $data)
    {
        if ($this->recordedPath) {
            $this->recordedPath->addData($data);
        }
    }

    /**
     * @param $data array
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }
}
