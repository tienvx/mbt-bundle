<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Nixilla\HipchatBundle\Service\HipchatNotifier;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Twig\Environment as Twig;

class HipchatReporter extends AbstractReporter
{
    /**
     * @var HipchatNotifier
     */
    protected $hipchat;

    /**
     * @var Twig
     */
    protected $twig;

    public function setHipchat(HipchatNotifier $hipChat)
    {
        $this->hipchat = $hipChat;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    public static function getName()
    {
        return 'hipchat';
    }

    /**
     * Send hipchat message about the bug.
     *
     * @param Bug $bug
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function report(Bug $bug)
    {
        if (!$this->hipchat) {
            throw new \Exception('Need to install tienvx/hipchat-bundle package to send hipchat message');
        }
        if (!$this->twig) {
            throw new \Exception('Need to install symfony/twig-bundle package to send hipchat message');
        }

        $message = $this->twig->render(
            '@TienvxMbt/bug-templates/default.html.twig',
            [
              'id'      => $bug->getId(),
              'task'    => $bug->getTask()->getTitle(),
              'message' => $bug->getBugMessage(),
              'steps'   => $this->buildSteps($bug),
              'status'  => $bug->getStatus(),
            ]
        );

        $this->hipchat->notify('purple', $message, 'html', false);
    }
}
