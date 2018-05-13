<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;

trait BugOutputTrait
{
    private function printBug(string $message, Path $path, OutputInterface $output)
    {
        $output->writeln(sprintf('Found a bug: %s', $message));

        $table = new Table($output);
        $table->setHeaders([
            [new TableCell('Steps to reproduce', ['colspan' => 3])],
            ['Step', 'Action', 'Data'],
        ]);
        foreach ($path->getEdges() as $index => $edge) {
            $table->addRow([$index + 1, $edge->getAttribute('label'), json_encode($path->getDataAt($index) ?? [])]);
        }
        $table->render();
    }
}
