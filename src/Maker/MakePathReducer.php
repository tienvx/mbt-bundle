<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakePathReducer extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:reducer';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new path reducer class')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the path reducer.')
            ->addArgument('reducer-class', InputArgument::OPTIONAL, sprintf('Choose a name for your path reducer class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakePathReducer.txt'))
        ;
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');
        $reducerClass = $input->getArgument('reducer-class');

        $reducerClassNameDetails = $generator->createClassNameDetails(
            $reducerClass,
            'PathReducer\\'
        );

        $generator->generateClass(
            $reducerClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/reducer/PathReducer.tpl.php',
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
