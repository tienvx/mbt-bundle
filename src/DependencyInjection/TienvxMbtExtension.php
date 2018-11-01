<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Generator\WeightedRandomGenerator;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerCommandConfiguration($config, $container);
        $this->registerGeneratorConfiguration($config, $container);
        $this->registerSubjectConfiguration($config, $container);

        $container->registerForAutoconfiguration(GeneratorInterface::class)
            ->setLazy(true)
            ->addTag('mbt.generator');
        $container->registerForAutoconfiguration(PathReducerInterface::class)
            ->setLazy(true)
            ->addTag('mbt.path_reducer');
    }

    private function registerCommandConfiguration(array $config, ContainerBuilder $container)
    {
        $executeTaskCommandDefinition = $container->getDefinition(ExecuteTaskCommand::class);
        $executeTaskCommandDefinition->addMethodCall('setDefaultBugTitle', [$config['command']['default_bug_title']]);
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
