<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class TableHelper
{
    public static function render(Path $path): string
    {
        $steps = [];
        foreach ($path as $index => $step) {
            $steps[] = [$index + 1, $step[0], json_encode($step[1]), implode(',', $step[2])];
        }

        $output = new BufferedOutput();

        $table = new Table($output);
        $table
            ->setHeaders(['Step', 'Transition', 'Data', 'Places'])
            ->setRows($steps)
        ;
        $table->render();

        return $output->fetch();
    }
}
