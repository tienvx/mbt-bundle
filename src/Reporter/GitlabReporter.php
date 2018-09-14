<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use GuzzleHttp\Client;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Twig\Environment as Twig;

class GitlabReporter implements ReporterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * @var string
     */
    protected $token;

    public function setHipchat(Client $client)
    {
        $this->client = $client;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setProjectId(int $projectId)
    {
        $this->projectId = $projectId;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
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
        if (!$this->client) {
            throw new \Exception('Need to install guzzlehttp/guzzle package to send hipchat message');
        }
        if (!$this->twig) {
            throw new \Exception('Need to install symfony/twig-bundle package to send hipchat message');
        }

        $description = $this->twig->render(
            '@TienvxMbt/bug-templates/default.html.twig',
            [
              'id'      => $bug->getId(),
              'task'    => $bug->getTask()->getTitle(),
              'message' => $bug->getBugMessage(),
              'steps'   => PathBuilder::build($bug->getPath()),
              'status'  => $bug->getStatus(),
            ]
        );

        $this->client->post(
            sprintf('%s/projects/%d/issues', $this->address, $this->projectId),
            [
                'headers' => [
                    'PRIVATE-TOKEN' => $this->token,
                ],
                'json' => [
                    'title' => $bug->getBugMessage(),
                    'description' => $description
                ]
            ]
        );
    }

    public static function getName()
    {
        return 'gitlab';
    }
}
