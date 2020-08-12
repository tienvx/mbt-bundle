<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

/**
 * @covers \Tienvx\Bundle\MbtBundle\TienvxMbtBundle
 */
class TienvxMbtBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = new ContainerBuilder();
        $oldPasses = $container->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();
        $bundle = new TienvxMbtBundle();
        $bundle->build($container);
        $newPasses = $container->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();
        $this->assertSame(1, count($newPasses) - count($oldPasses));
        $hasPass = false;
        foreach ($newPasses as $pass) {
            if ($pass instanceof PluginPass) {
                $hasPass = true;
            }
        }
        $this->assertTrue($hasPass);
    }
}
