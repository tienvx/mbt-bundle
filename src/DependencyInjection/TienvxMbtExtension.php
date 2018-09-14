<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use GuzzleHttp\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Swift_Mailer;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Generator\WeightedRandomGenerator;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerInterface;
use Tienvx\Bundle\MbtBundle\Reporter\EmailReporter;
use Tienvx\Bundle\MbtBundle\Reporter\GithubReporter;
use Tienvx\Bundle\MbtBundle\Reporter\GitlabReporter;
use Tienvx\Bundle\MbtBundle\Reporter\HipchatReporter;
use Tienvx\Bundle\MbtBundle\Reporter\JiraReporter;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;
use Tienvx\Bundle\MbtBundle\Reporter\SlackReporter;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Twig\Environment as Twig;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerReporterConfiguration($config, $container);
        $this->registerGeneratorConfiguration($config, $container);
        $this->registerSubjectConfiguration($config, $container);

        $executeTaskCommandDefinition = $container->getDefinition(ExecuteTaskCommand::class);
        $executeTaskCommandDefinition->addMethodCall('setDefaultBugTitle', [$config['default_bug_title']]);

        $container->registerForAutoconfiguration(GeneratorInterface::class)
            ->setLazy(true)
            ->addTag('mbt.generator');
        $container->registerForAutoconfiguration(PathReducerInterface::class)
            ->setLazy(true)
            ->addTag('mbt.path_reducer');
        $container->registerForAutoconfiguration(ReporterInterface::class)
            ->setLazy(true)
            ->addTag('mbt.reporter');
    }

    private function registerReporterConfiguration(array $config, ContainerBuilder $container)
    {
        $emailReporterDefinition = $container->getDefinition(EmailReporter::class);
        $emailReporterDefinition->addMethodCall('setFrom', [$config['reporter']['email']['from']]);
        $emailReporterDefinition->addMethodCall('setTo', [$config['reporter']['email']['to']]);
        if (class_exists(Swift_Mailer::class)) {
            $emailReporterDefinition->addMethodCall('setMailer', [new Reference(Swift_Mailer::class)]);
        }
        if (class_exists(Twig::class)) {
            $emailReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $hipchatReporterDefinition = $container->getDefinition(HipchatReporter::class);
        $hipchatReporterDefinition->addMethodCall('setAddress', [$config['reporter']['hipchat']['address']]);
        $hipchatReporterDefinition->addMethodCall('setRoom', [$config['reporter']['hipchat']['room']]);
        $hipchatReporterDefinition->addMethodCall('setToken', [$config['reporter']['hipchat']['token']]);
        $hipchatReporterDefinition->addMethodCall('setColor', [$config['reporter']['hipchat']['color']]);
        $hipchatReporterDefinition->addMethodCall('setNotify', [$config['reporter']['hipchat']['notify']]);
        $hipchatReporterDefinition->addMethodCall('setFormat', [$config['reporter']['hipchat']['format']]);
        if (class_exists(Client::class)) {
            $hipchatReporterDefinition->addMethodCall('setClient', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $hipchatReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $slackReporterDefinition = $container->getDefinition(SlackReporter::class);
        $slackReporterDefinition->addMethodCall('setAddress', [$config['reporter']['slack']['address']]);
        $slackReporterDefinition->addMethodCall('setChannel', [$config['reporter']['slack']['channel']]);
        $slackReporterDefinition->addMethodCall('setToken', [$config['reporter']['slack']['token']]);
        if (class_exists(Client::class)) {
            $slackReporterDefinition->addMethodCall('setClient', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $slackReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $githubReporterDefinition = $container->getDefinition(GithubReporter::class);
        $githubReporterDefinition->addMethodCall('setAddress', [$config['reporter']['github']['address']]);
        $githubReporterDefinition->addMethodCall('setRepoOwner', [$config['reporter']['github']['repoOwner']]);
        $githubReporterDefinition->addMethodCall('setRepoName', [$config['reporter']['github']['repoName']]);
        $githubReporterDefinition->addMethodCall('setToken', [$config['reporter']['github']['token']]);
        if (class_exists(Client::class)) {
            $githubReporterDefinition->addMethodCall('setClient', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $githubReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $gitlabReporterDefinition = $container->getDefinition(GitlabReporter::class);
        $gitlabReporterDefinition->addMethodCall('setAddress', [$config['reporter']['gitlab']['address']]);
        $gitlabReporterDefinition->addMethodCall('setProjectId', [$config['reporter']['gitlab']['projectId']]);
        $gitlabReporterDefinition->addMethodCall('setToken', [$config['reporter']['gitlab']['token']]);
        if (class_exists(Client::class)) {
            $gitlabReporterDefinition->addMethodCall('setClient', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $gitlabReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $jiraReporterDefinition = $container->getDefinition(JiraReporter::class);
        $jiraReporterDefinition->addMethodCall('setAddress', [$config['reporter']['jira']['address']]);
        $jiraReporterDefinition->addMethodCall('setProjectId', [$config['reporter']['jira']['projectId']]);
        $jiraReporterDefinition->addMethodCall('setIssueType', [$config['reporter']['jira']['issueType']]);
        $jiraReporterDefinition->addMethodCall('setUsername', [$config['reporter']['jira']['username']]);
        $jiraReporterDefinition->addMethodCall('setPassword', [$config['reporter']['jira']['password']]);
        if (class_exists(Client::class)) {
            $jiraReporterDefinition->addMethodCall('setClient', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $jiraReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }
    }

    private function registerGeneratorConfiguration(array $config, ContainerBuilder $container)
    {
        $randomGeneratorDefinition = $container->getDefinition(RandomGenerator::class);
        $randomGeneratorDefinition->addMethodCall('setMaxPathLength', [$config['generator']['max_path_length']]);
        $randomGeneratorDefinition->addMethodCall('setTransitionCoverage', [$config['generator']['transition_coverage']]);
        $randomGeneratorDefinition->addMethodCall('setPlaceCoverage', [$config['generator']['place_coverage']]);

        $weightedRandomGeneratorDefinition = $container->getDefinition(WeightedRandomGenerator::class);
        $weightedRandomGeneratorDefinition->addMethodCall('setMaxPathLength', [$config['generator']['max_path_length']]);
    }

    private function registerSubjectConfiguration(array $config, ContainerBuilder $container)
    {
        $subjectManagerDefinition = $container->getDefinition(SubjectManager::class);
        $subjectManagerDefinition->addMethodCall('addSubjects', [$config['subjects']]);
    }
}
