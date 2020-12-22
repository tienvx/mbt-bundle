<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverPlatform;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class Selenoid extends AbstractProvider
{
    public static function getName(): string
    {
        return 'selenoid';
    }

    /**
     * @throws ExceptionInterface
     */
    public function getVideoUrl(string $seleniumServer, int $bugId): string
    {
        return sprintf('%s/video/bug-%s.mp4', $seleniumServer, $bugId);
    }

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities
    {
        $caps = [];
        if ($recordVideoBugId) {
            $caps += [
                'enableVideo' => true,
                'videoName' => sprintf('bug-%d.mp4', $recordVideoBugId),
                'name' => sprintf('Recording video for bug %d', $recordVideoBugId),
            ];
        }
        if (WebDriverPlatform::ANDROID === $task->getSeleniumConfig()->getPlatform()) {
            $caps += [
                'skin' => $task->getSeleniumConfig()->getResolution(),
            ];
        } else {
            $caps += [
                'screenResolution' => "{$task->getSeleniumConfig()->getResolution()}x24",
            ];
        }
        $caps += [
            WebDriverCapabilityType::BROWSER_NAME => $task->getSeleniumConfig()->getBrowser(),
            WebDriverCapabilityType::VERSION => $task->getSeleniumConfig()->getBrowserVersion(),
            WebDriverCapabilityType::PLATFORM => $task->getSeleniumConfig()->getPlatform(),
            'enableVNC' => true,
            'enableLog' => true,
            'name' => sprintf('Executing task %d', $task->getId()),
        ];

        return new DesiredCapabilities($caps);
    }

    protected function loadConfig(): array
    {
        return require __DIR__ . '/../Resources/providers/selenoid.php';
    }
}
