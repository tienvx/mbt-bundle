<?php

namespace Tienvx\Bundle\MbtBundle\Formatter;

use Monolog\Formatter\HtmlFormatter;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class BugFormatter extends HtmlFormatter
{
    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = $this->addTitle($record['level_name'], $record['level']);
        $output .= '<table cellspacing="1" width="100%" class="monolog-output">';

        if (isset($record['context']['bug'])) {
            $bug = $record['context']['bug'];
            if ($bug instanceof Bug) {
                $output .= $this->addRow('Bug ID', $bug->getId());
                $output .= $this->addRow('Bug Title', $bug->getTitle());
                $output .= $this->addRow('Task Title', $bug->getTask()->getTitle());
                $output .= $this->addRow('Bug Message', $record['message']);
            }
            if (isset($record['context']['path'])) {
                $path = $record['context']['path'];
                if ($path instanceof Path) {
                    $embeddedTable = '<table cellspacing="1" width="100%">';
                    foreach ($path as $index => $step) {
                        $embeddedTable .= $this->addRow('Step', $this->convertToString($index + 1));
                        $embeddedTable .= $this->addRow('Transition', $this->convertToString($step[0]));
                        $embeddedTable .= $this->addRow('Data', $this->convertToString($step[1]));
                        $embeddedTable .= $this->addRow('Places', $this->convertToString(implode('|', $step[2])));

                        $transitionName = $step[0];
                        $subject = $record['context']['subject'];
                        if ($transitionName && $subject instanceof AbstractSubject) {
                            $url = $subject->getScreenshotUrl($bug->getId(), $index);
                            if ($url) {
                                $embeddedTable .= $this->addRow('Screenshot', $this->addImage($url), false);
                            }
                        }
                    }
                    $embeddedTable .= '</table>';
                    $output .= $this->addRow('Steps', $embeddedTable, false);
                }
            }
        }

        return $output.'</table>';
    }

    public function addImage($src)
    {
        // Similar to how mbt-admin work
        return "<a href=\"{$src}\" target=\"_blank\" rel=\"noreferrer noopener\"><img src=\"{$src}\" alt=\"Screenshot\"></a>";
    }
}
