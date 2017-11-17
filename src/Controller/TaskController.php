<?php

namespace Tienvx\Bundle\MbtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskController extends Controller
{
    public function startAction()
    {
        return $this->render('TienvxMbtBundle:Task:start.html.twig');
    }

    public function stopAction()
    {
        return $this->render('TienvxMbtBundle:Task:stop.html.twig');
    }
}
