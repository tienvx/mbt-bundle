<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Dumper;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class GraphvizDumper
{
    protected static $defaultOptions = array(
        'graph' => array('ratio' => 'compress', 'rankdir' => 'LR'),
        'node' => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => '#333333', 'fillcolor' => 'lightblue', 'fixedsize' => 'false', 'width' => 1),
        'edge' => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => '#333333', 'arrowhead' => 'normal', 'arrowsize' => 0.5),
    );

    /**
     * Dumps the workflow as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes (places)
     *  * edge: The default options for edges
     */
    public function dump(string $initialPlaces, Graph $graph, array $options = array())
    {
        $places = $this->findPlaces($initialPlaces, $graph);
        $edges = $this->findEdges($graph);

        $options = array_replace_recursive(self::$defaultOptions, $options);

        return $this->startDot($options)
            .$this->addPlaces($places)
            .$this->addEdges($edges)
            .$this->endDot()
        ;
    }

    /**
     * @internal
     */
    protected function findPlaces(string $initialPlaces, Graph $graph)
    {
        $places = array();

        /** @var Vertex $vertex */
        foreach ($graph->getVertices() as $vertex) {
            $attributes = array();
            if ($vertex->getId() === $initialPlaces) {
                $attributes['style'] = 'filled';
            }
            $places[$vertex->getId()] = array(
                'attributes' => $attributes,
            );
        }

        return $places;
    }

    /**
     * @internal
     */
    protected function addPlaces(array $places)
    {
        $code = '';

        foreach ($places as $id => $place) {
            $code .= sprintf("  place_%s [label=\"%s\", shape=circle%s];\n", $this->dotize($id), $this->escape($id), $this->addAttributes($place['attributes']));
        }

        return $code;
    }

    /**
     * @internal
     */
    protected function findEdges(Graph $graph)
    {
        $edges = array();

        /** @var Directed $edge */
        foreach ($graph->getEdges() as $edge) {
            $edges[$edge->getVertexStart()->getId()][] = array(
                'name' => $edge->getAttribute('name'),
                'to' => $edge->getVertexEnd()->getId(),
            );
        }

        return $edges;
    }

    /**
     * @internal
     */
    protected function addEdges(array $edges)
    {
        $code = '';

        foreach ($edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= sprintf("  place_%s -> place_%s [label=\"%s\" style=\"%s\"];\n", $this->dotize($id), $this->dotize($edge['to']), $this->escape($edge['name']), 'solid');
            }
        }

        return $code;
    }

    /**
     * @internal
     */
    protected function startDot(array $options)
    {
        return sprintf(
            "digraph workflow {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($options['graph']),
            $this->addOptions($options['node']),
            $this->addOptions($options['edge'])
        );
    }

    /**
     * @internal
     */
    protected function endDot()
    {
        return "}\n";
    }

    /**
     * @internal
     */
    protected function dotize($id)
    {
        return hash('sha1', $id);
    }

    /**
     * @internal
     */
    protected function escape(string $string): string
    {
        return addslashes($string);
    }

    private function addAttributes(array $attributes): string
    {
        $code = array();

        foreach ($attributes as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $this->escape($v));
        }

        return $code ? ', '.implode(', ', $code) : '';
    }

    private function addOptions(array $options): string
    {
        $code = array();

        foreach ($options as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return implode(' ', $code);
    }
}
