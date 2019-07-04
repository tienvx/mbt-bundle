<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeReporter extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:reporter';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new reporter class')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the reporter.')
            ->addArgument('reporter-class', InputArgument::OPTIONAL, sprintf('Choose a name for your reporter class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeReporter.txt'))
        ;
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     *
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');
        $reporterClass = $input->getArgument('reporter-class');

        $reporterClassNameDetails = $generator->createClassNameDetails(
            $reporterClass,
            'Reporter\\'
        );

        $generator->generateClass(
            $reporterClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/reporter/Reporter.php.tpl',
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
