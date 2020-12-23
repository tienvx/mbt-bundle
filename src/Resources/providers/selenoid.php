<?php

use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use Tienvx\Bundle\MbtBundle\Provider\ProviderInterface;

$pointZero = fn ($version) => "$version.0";

return [
    WebDriverPlatform::LINUX => [
        ProviderInterface::BROWSERS => [
            WebDriverBrowserType::CHROME => array_map($pointZero, [...range(48, 81), ...range(83, 87)]),
            WebDriverBrowserType::FIREFOX => array_map($pointZero, range(48, 83)),
            WebDriverBrowserType::MICROSOFT_EDGE => ['88.0'],
            WebDriverBrowserType::OPERA => array_map($pointZero, [...range(33, 58), 60, ...range(62, 72)]),
        ],
        ProviderInterface::RESOLUTIONS => [
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
        ProviderInterface::BROWSERS => [
            WebDriverBrowserType::ANDROID => ['4.4', '5.0', '5.1', '6.0', '7.0', '7.1', '8.0', '8.1', '9.0', '10.0'],
            WebDriverBrowserType::CHROME => array_map($pointZero, [...range(73, 81), ...range(83, 86)]),
        ],
        ProviderInterface::RESOLUTIONS => [
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
];
