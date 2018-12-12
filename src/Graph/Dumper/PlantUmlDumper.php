<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Dumper;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class PlantUmlDumper implements DumperInterface
{
    private const INITIAL = '<<initial>>';
    private const MARKED = '<<marked>>';

    const STATEMACHINE_TRANSITION = 'arrow';
    const DEFAULT_OPTIONS = array(
        'skinparams' => array(
            'titleBorderRoundCorner' => 15,
            'titleBorderThickness' => 2,
            'state' => array(
                'BackgroundColor'.self::INITIAL => '#87b741',
                'BackgroundColor'.self::MARKED => '#3887C6',
                'BorderColor' => '#3887C6',
                'BorderColor'.self::MARKED => 'Black',
                'FontColor'.self::MARKED => 'White',
            ),
        ),
    );

    public function dump(string $initialPlaces, Graph $graph, array $options = array()): string
    {
        $options = array_replace_recursive(self::DEFAULT_OPTIONS, $options);
        $code = $this->initialize($options);
        /** @var Vertex $vertex */
        foreach ($graph->getVertices() as $vertex) {
            $placeEscaped = $this->escape($vertex->getId());
            $code[] = "state $placeEscaped" . ($initialPlaces === $vertex->getId() ? ' '.self::INITIAL : '');
        }
        /** @var Directed $edge */
        foreach ($graph->getEdges() as $edge) {
            $fromEscaped = $this->escape($edge->getVertexStart()->getId());
            $toEscaped = $this->escape($edge->getVertexEnd()->getId());
            $transitionEscaped = $this->escape($edge->getAttribute('name'));
            $code[] = "$fromEscaped --> $toEscaped: $transitionEscaped";
        }

        return $this->startPuml($options).$this->getLines($code).$this->endPuml($options);
    }

    private function startPuml(array $options): string
    {
        $start = '@startuml'.PHP_EOL;
        $start .= 'allow_mixing'.PHP_EOL;

        return $start;
    }

    private function endPuml(array $options): string
    {
        return PHP_EOL.'@enduml';
    }

    private function getLines(array $code): string
    {
        return implode(PHP_EOL, $code);
    }

    private function initialize(array $options): array
    {
        $code = array();
        if (isset($options['title'])) {
            $code[] = "title {$options['title']}";
        }
        if (isset($options['name'])) {
            $code[] = "title {$options['name']}";
        }
        if (isset($options['skinparams']) && \is_array($options['skinparams'])) {
            foreach ($options['skinparams'] as $skinparamKey => $skinparamValue) {
                if (!\is_array($skinparamValue)) {
                    $code[] = "skinparam {$skinparamKey} $skinparamValue";
                    continue;
                }
                $code[] = "skinparam {$skinparamKey} {";
                foreach ($skinparamValue as $key => $value) {
                    $code[] = "    {$key} $value";
                }
                $code[] = '}';
            }
        }

        return $code;
    }

    private function escape(string $string): string
    {
        // It's not possible to escape property double quote, so let's remove it
        return '"'.str_replace('"', '', $string).'"';
    }
}
