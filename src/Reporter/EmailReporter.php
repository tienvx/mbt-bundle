<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Swift_Mailer;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class EmailReporter extends AbstractReporter
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var mixed
     */
    protected $from;

    /**
     * @var mixed
     */
    protected $to;

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send email about the bug.
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
        if (!$this->mailer) {
            throw new \Exception('Need to install symfony/swiftmailer-bundle package to send email');
        }
        if (!$this->twig) {
            throw new \Exception('Need to install symfony/twig-bundle package to send email');
        }

        $body = $this->render($bug);

        $this->mailer->send(
            (new \Swift_Message($bug->getTitle()))
                ->setTo($this->to)
                ->setFrom($this->from)
                ->setBody($body, 'text/html')
        );
    }

    public static function getName()
    {
        return 'email';
    }
}
