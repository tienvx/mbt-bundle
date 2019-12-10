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

class MakeModel extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:model';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->setDescription('Creates a new model (workflow) configuration file')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the model (workflow).')
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeModel.txt'))
        ;
    }

    /**
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $name = $input->getArgument('name');

        $generator->generateFile(
            'config/packages/models/'.$name.'.yaml',
            __DIR__.'/../Resources/skeleton/model/model.yaml.tpl',
            [
                'name' => $name,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
