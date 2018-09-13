<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use GuzzleHttp\Client;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Twig\Environment as Twig;

class GithubReporter implements ReporterInterface
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
     * @var string
     */
    protected $repoOwner;

    /**
     * @var string
     */
    protected $repoName;

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

    public function setRepoOwner(string $repoOwner)
    {
        $this->repoOwner = $repoOwner;
    }

    public function setRepoName(string $repoName)
    {
        $this->repoName = $repoName;
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

        $body = $this->twig->render(
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
            sprintf('%s/repos/%s/%s/issues', $this->address, $this->repoOwner, $this->repoName),
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->token,
                ],
                'json' => [
                    'title' => $bug->getBugMessage(),
                    'body' => $body
                ]
            ]
        );
    }

    public static function getName()
    {
        return 'github';
    }
}
