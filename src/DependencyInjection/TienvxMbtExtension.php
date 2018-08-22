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
use Tienvx\Bundle\MbtBundle\Reporter\HipchatReporter;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;
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

        $emailReporterDefinition = $container->getDefinition(EmailReporter::class);
        $emailReporterDefinition->addMethodCall('setFrom', [$config['reporter']['email']['from']]);
        $emailReporterDefinition->addMethodCall('setTo', [$config['reporter']['email']['to']]);
        if (class_exists(Swift_Mailer::class)) {
            $emailReporterDefinition->addMethodCall('setMailer', [new Reference(Swift_Mailer::class)]);
        }
        if (class_exists(Twig::class)) {
            $emailReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $executeTaskCommandDefinition = $container->getDefinition(ExecuteTaskCommand::class);
        $executeTaskCommandDefinition->addMethodCall('setDefaultBugTitle', [$config['default_bug_title']]);

        $randomGeneratorDefinition = $container->getDefinition(RandomGenerator::class);
        $randomGeneratorDefinition->addMethodCall('setMaxPathLength', [$config['max_path_length']]);
        $randomGeneratorDefinition->addMethodCall('setTransitionCoverage', [$config['transition_coverage']]);
        $randomGeneratorDefinition->addMethodCall('setPlaceCoverage', [$config['place_coverage']]);

        $weightedRandomGeneratorDefinition = $container->getDefinition(WeightedRandomGenerator::class);
        $weightedRandomGeneratorDefinition->addMethodCall('setMaxPathLength', [$config['max_path_length']]);

        $hipchatReporterDefinition = $container->getDefinition(HipchatReporter::class);
        $hipchatReporterDefinition->addMethodCall('setAddress', [$config['reporter']['hipchat']['address']]);
        $hipchatReporterDefinition->addMethodCall('setRoom', [$config['reporter']['hipchat']['room']]);
        $hipchatReporterDefinition->addMethodCall('setToken', [$config['reporter']['hipchat']['token']]);
        $hipchatReporterDefinition->addMethodCall('setColor', [$config['reporter']['hipchat']['color']]);
        $hipchatReporterDefinition->addMethodCall('setNotify', [$config['reporter']['hipchat']['notify']]);
        $hipchatReporterDefinition->addMethodCall('setFormat', [$config['reporter']['hipchat']['format']]);
        if (class_exists(Client::class)) {
            $hipchatReporterDefinition->addMethodCall('setHipchat', [new Reference(Client::class)]);
        }
        if (class_exists(Twig::class)) {
            $hipchatReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $subjectManagerDefinition = $container->getDefinition(SubjectManager::class);
        $subjectManagerDefinition->addMethodCall('addSubjects', [$config['subjects']]);

        $container->registerForAutoconfiguration(GeneratorInterface::class)
            ->setLazy(true)
            ->addTag('mbt.generator');
        $container->registerForAutoconfiguration(StopConditionInterface::class)
            ->setLazy(true)
            ->addTag('mbt.stop_condition');
        $container->registerForAutoconfiguration(PathReducerInterface::class)
            ->setLazy(true)
            ->addTag('mbt.path_reducer');
        $container->registerForAutoconfiguration(ReporterInterface::class)
            ->setLazy(true)
            ->addTag('mbt.reporter');
    }
}
