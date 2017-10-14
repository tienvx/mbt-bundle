<?php

namespace Tienvx\Bundle\MbtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TienvxMbtBundle:Default:index.html.twig');
    }
}
