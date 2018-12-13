<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class GraphDumpCommandTest extends CommandTestCase
{
    public function modelData()
    {
        $articleDot =
'digraph workflow {
  ratio="compress" rankdir="LR" label="Article"
  node [fontsize="9" fontname="Arial" color="#333333" fillcolor="lightblue" fixedsize="false" width="1"];
  edge [fontsize="9" fontname="Arial" color="#333333" arrowhead="normal" arrowsize="0.5"];

  place_5916096c35437dab3f6a120790f2a431656affb5 [label="[\"draft\"]", shape=circle, style="filled"];
  place_9e77ae793650b6bd67da69003adb32a9b17eb712 [label="[\"wait_for_journalist\",\"wait_for_spellchecker\"]", shape=circle];
  place_03ef92587dde689543f7a2bfaa85e3744e9627f0 [label="[\"wait_for_journalist\"]", shape=circle];
  place_ef8332535747bd04b47faa11e2d6d8a767585208 [label="[\"approved_by_journalist\"]", shape=circle];
  place_429a3f35b9bcae743353aa5f800f0bda20c6c5e7 [label="[\"approved_by_journalist\",\"wait_for_spellchecker\"]", shape=circle];
  place_40a974ae46330f7c0eaf9998260b86bddde9f616 [label="[\"wait_for_spellchecker\"]", shape=circle];
  place_9d8d5371bae52cae02416988609683522d86ab14 [label="[\"approved_by_spellchecker\"]", shape=circle];
  place_37e58158cff8e991ff736f76ef5256dc8b4ae850 [label="[\"approved_by_spellchecker\",\"wait_for_journalist\"]", shape=circle];
  place_309776058fe7a6debc12bc716d2cac6a3167eb2a [label="[\"approved_by_journalist\",\"approved_by_spellchecker\"]", shape=circle];
  place_0176512e721e0f9b27d66831090c2408c3c4a040 [label="[\"published\"]", shape=circle];
  place_5916096c35437dab3f6a120790f2a431656affb5 -> place_9e77ae793650b6bd67da69003adb32a9b17eb712 [label="request_review" style="solid"];
  place_03ef92587dde689543f7a2bfaa85e3744e9627f0 -> place_ef8332535747bd04b47faa11e2d6d8a767585208 [label="journalist_approval" style="solid"];
  place_9e77ae793650b6bd67da69003adb32a9b17eb712 -> place_429a3f35b9bcae743353aa5f800f0bda20c6c5e7 [label="journalist_approval" style="solid"];
  place_9e77ae793650b6bd67da69003adb32a9b17eb712 -> place_37e58158cff8e991ff736f76ef5256dc8b4ae850 [label="spellchecker_approval" style="solid"];
  place_40a974ae46330f7c0eaf9998260b86bddde9f616 -> place_9d8d5371bae52cae02416988609683522d86ab14 [label="spellchecker_approval" style="solid"];
  place_429a3f35b9bcae743353aa5f800f0bda20c6c5e7 -> place_309776058fe7a6debc12bc716d2cac6a3167eb2a [label="spellchecker_approval" style="solid"];
  place_309776058fe7a6debc12bc716d2cac6a3167eb2a -> place_0176512e721e0f9b27d66831090c2408c3c4a040 [label="publish" style="solid"];
  place_37e58158cff8e991ff736f76ef5256dc8b4ae850 -> place_309776058fe7a6debc12bc716d2cac6a3167eb2a [label="journalist_approval" style="solid"];
}

';
        $pullRequestPuml =
'@startuml
allow_mixing
title pull_request
skinparam titleBorderRoundCorner 15
skinparam titleBorderThickness 2
skinparam state {
    BackgroundColor<<initial>> #87b741
    BackgroundColor<<marked>> #3887C6
    BorderColor #3887C6
    BorderColor<<marked>> Black
    FontColor<<marked>> White
}
state "start" <<initial>>
state "coding"
state "travis"
state "review"
state "merged"
state "closed"
"start" --> "travis": "submit"
"coding" --> "travis": "update"
"travis" --> "travis": "update"
"review" --> "travis": "update"
"travis" --> "review": "wait_for_review"
"review" --> "coding": "request_change"
"review" --> "merged": "accept"
"review" --> "closed": "reject"
"closed" --> "review": "reopen"
@enduml
';
        return [
            ['article', 'Article', 'dot', $articleDot],
            ['pull_request', 'Pull Request', 'puml', $pullRequestPuml],
        ];
    }

    /**
     * @dataProvider modelData
     * @param string $model
     * @param string $label
     * @param string $format
     * @param string $output
     */
    public function testExecute(string $model, string $label, string $format, string $output)
    {
        $command = $this->application->find('mbt:graph:dump');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'       => $command->getName(),
            'name'          => $model,
            '--label'       => $label,
            '--dump-format' => $format,
        ]);

        $this->assertEquals($output, $commandTester->getDisplay());
    }
}
