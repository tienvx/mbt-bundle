<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Exception;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeReducer extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:reducer';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new reducer class')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the reducer.')
            ->addArgument('reducer-class', InputArgument::OPTIONAL, sprintf('Choose a name for your reducer class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeReducer.txt'))
        ;
    }

    /**
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');
        $reducerClass = $input->getArgument('reducer-class');

        $reducerClassNameDetails = $generator->createClassNameDetails(
            $reducerClass,
            'Reducer\\'
        );

        $generator->generateClass(
            $reducerClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/reducer/Reducer.php.tpl',
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
