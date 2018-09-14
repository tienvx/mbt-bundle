<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

class JiraReporter extends AbstractReporter
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $projectId;

    /**
     * @var string
     */
    protected $issueType;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setProjectId(string $projectId)
    {
        $this->projectId = $projectId;
    }

    public function setIssueType(string $issueType)
    {
        $this->issueType = $issueType;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
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

        $body = $this->render($bug);

        // https://developer.atlassian.com/cloud/jira/platform/rest/v3/#api-api-3-issue-post
        $this->client->post(
            sprintf('%s/issue', $this->address),
            [
                'auth' => [
                    $this->username,
                    $this->password
                ],
                'json' => [
                    'fields' => [
                        'project' => [
                            'id' => $this->projectId
                        ],
                        'issuetype' => [
                            'id' => $this->issueType
                        ],
                    ],
                    'summary' => $bug->getBugMessage(),
                    'description' => [
                        'type' => 'text',
                        'text' => $body,
                    ],
                ]
            ]
        );
    }

    public static function getName()
    {
        return 'jira';
    }
}
