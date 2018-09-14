<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

class GitlabReporter extends AbstractReporter
{
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
        $this->check();

        $description = $this->render($bug);

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
