<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Exception;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakePredefinedCase extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:predefined-case';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new pre-defined case (test case) configuration file')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the pre-defined case (test case).')
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakePredefinedCase.txt'))
        ;
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     *
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');

        $generator->generateFile(
            'config/packages/predefined-cases/'.$name.'.yaml',
            __DIR__.'/../Resources/skeleton/predefined-case/predefined-case.yaml.tpl',
            [
                'name' => $name,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }
}
