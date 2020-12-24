<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures;

use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration;

class Config
{
    public const DEFAULT_CONFIG = [
        Configuration::MAX_STEPS => 150,
        Configuration::PROVIDERS => [
            'selenoid' => [
                Configuration::SELENIUM_SERVER => 'http://localhost:4444',
                Configuration::PLATFORMS => [
                    WebDriverPlatform::LINUX => [
                        Configuration::BROWSERS => [
                            WebDriverBrowserType::CHROME => [
                                Configuration::VERSIONS => ['87.0'],
                            ],
                            WebDriverBrowserType::FIREFOX => [
                                Configuration::VERSIONS => ['83.0'],
                            ],
                            WebDriverBrowserType::MICROSOFT_EDGE => [
                                Configuration::VERSIONS => ['89.0'],
                            ],
                            WebDriverBrowserType::OPERA => [
                                Configuration::VERSIONS => ['72.0'],
                            ],
                        ],
                        Configuration::RESOLUTIONS => [
                            '1024x768',
                            '1280x800',
                            '1280x1024',
                            '1366x768',
                            '1440x900',
                            '1680x1050',
                            '1600x1200',
                            '1920x1080',
                            '2048x1536',
                        ],
                    ],
                    WebDriverPlatform::ANDROID => [
                        Configuration::BROWSERS => [
                            WebDriverBrowserType::ANDROID => [
                                Configuration::VERSIONS => ['10.0'],
                            ],
                            WebDriverBrowserType::CHROME => [
                                Configuration::VERSIONS => ['86.0'],
                            ],
                        ],
                        Configuration::RESOLUTIONS => [
                            '240x320',
                            '240x400',
                            '240x432',
                            '320x480',
                            '480x800',
                            '480x854',
                            '1024x600',
                            '720x1280',
                            '1280x800',
                            '800x1280',
                        ],
                    ],
                ],
            ],
        ],
    ];
}
