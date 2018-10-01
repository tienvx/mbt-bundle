<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use GuzzleHttp\Client;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Twig\Environment as Twig;

abstract class AbstractReporter implements ReporterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Twig
     */
    protected $twig;

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws Exception
     */
    protected function check()
    {
        if (!$this->client) {
            throw new Exception('Need to install guzzlehttp/guzzle package to send hipchat message');
        }
        if (!$this->twig) {
            throw new Exception('Need to install symfony/twig-bundle package to send hipchat message');
        }
    }

    /**
     * @param Bug $bug
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws Exception
     */
    protected function render(Bug $bug)
    {
        return $this->twig->render(
            '@TienvxMbt/bug-templates/default.html.twig',
            [
              'id'      => $bug->getId(),
              'task'    => $bug->getTask()->getTitle(),
              'message' => $bug->getBugMessage(),
              'steps'   => PathBuilder::build($bug->getPath()),
              'status'  => $bug->getStatus(),
            ]
        );
    }
}
