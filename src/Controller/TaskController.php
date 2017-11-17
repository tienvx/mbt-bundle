<?php

namespace Tienvx\Bundle\MbtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskController extends Controller
{
    public function startAction()
    {
        return $this->render('@TienvxMbt/Task/start.html.twig');
    }

    public function stopAction()
    {
        return $this->render('@TienvxMbt/Task/stop.html.twig');
    }
}
