<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

#[Autoconfigure(tags: [CommandRunnerInterface::TAG], lazy: true)]
interface CommandRunnerInterface
{
    public const MECHANISM_ID = 'id';
    public const MECHANISM_NAME = 'name';
    public const MECHANISM_LINK_TEXT = 'linkText';
    public const MECHANISM_PARTIAL_LINK_TEXT = 'partialLinkText';
    public const MECHANISM_XPATH = 'xpath';
    public const MECHANISM_CSS = 'css';
    public const MECHANISMS = [
        CommandRunnerInterface::MECHANISM_ID,
        CommandRunnerInterface::MECHANISM_NAME,
        CommandRunnerInterface::MECHANISM_LINK_TEXT,
        CommandRunnerInterface::MECHANISM_PARTIAL_LINK_TEXT,
        CommandRunnerInterface::MECHANISM_XPATH,
        CommandRunnerInterface::MECHANISM_CSS,
    ];

    public const TAG = 'mbt_bundle.command_runner';

    public function getAllCommands(): array;

    public function getCommandsRequireTarget(): array;

    public function getCommandsRequireValue(): array;

    public function validateTarget(CommandInterface $command): bool;

    public function supports(CommandInterface $command): bool;

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void;
}
