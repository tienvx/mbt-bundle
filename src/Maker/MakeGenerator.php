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

final class MakeGenerator extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:generator';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->setDescription('Creates a new generator class')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the generator.')
            ->addArgument('generator-class', InputArgument::OPTIONAL, sprintf('Choose a name for your generator class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeGenerator.txt'))
        ;
    }

    /**
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $name = $input->getArgument('name');
        $generatorClass = $input->getArgument('generator-class');

        $generatorClassNameDetails = $generator->createClassNameDetails(
            $generatorClass,
            'Generator\\'
        );

        $generator->generateClass(
            $generatorClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/generator/Generator.php.tpl',
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
