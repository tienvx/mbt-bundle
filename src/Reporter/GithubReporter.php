<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class GithubReporter extends AbstractReporter
{
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
     * @throws Exception
     */
    public function report(Bug $bug)
    {
        $this->check();

        $body = $this->render($bug);

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
