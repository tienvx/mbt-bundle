<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Command\CaptureScreenshotsCommand;
use Tienvx\Bundle\MbtBundle\Command\RemoveScreenshotsCommand;
use Tienvx\Bundle\MbtBundle\Command\ReportBugCommand;

class FlysystemFilesystemPass implements CompilerPassInterface
{
    private $filesystemTag;

    public function __construct(string $filesystemTag = 'oneup_flysystem.mbt_filesystem')
    {
        $this->filesystemTag = $filesystemTag;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition($this->filesystemTag)) {
            $filesystem = new Reference($this->filesystemTag);

            $reportBugCommandDefinition = $container->getDefinition(ReportBugCommand::class);
            $reportBugCommandDefinition->addMethodCall('setFilesystem', [$filesystem]);

            $captureScreenshotsCommandDefinition = $container->getDefinition(CaptureScreenshotsCommand::class);
            $captureScreenshotsCommandDefinition->addMethodCall('setFilesystem', [$filesystem]);

            $removeScreenshotsCommandDefinition = $container->getDefinition(RemoveScreenshotsCommand::class);
            $removeScreenshotsCommandDefinition->addMethodCall('setFilesystem', [$filesystem]);
        }
    }
}
