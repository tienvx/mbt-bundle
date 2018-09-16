<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Psr\Log\LoggerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class LoggerReporter extends AbstractReporter
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Send hipchat message about the bug.
     *
     * @param Bug $bug
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws Exception
     */
    public function report(Bug $bug)
    {
        if (!$this->logger) {
            throw new Exception('Need to install symfony/monolog-bundle package to log bug');
        }
        if (!$this->twig) {
            throw new Exception('Need to install symfony/twig-bundle package to log bug');
        }

        $this->logger->error($this->render($bug));
    }

    public static function getName()
    {
        return 'logger';
    }
}
