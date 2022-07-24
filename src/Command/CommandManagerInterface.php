<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\Alert\AcceptAlertCommand;
use Tienvx\Bundle\MbtBundle\Command\Alert\AnswerPromptCommand;
use Tienvx\Bundle\MbtBundle\Command\Alert\DismissPromptCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertAlertCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertCheckedCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertEditableCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertElementNotPresentCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertElementPresentCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotCheckedCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotEditableCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedLabelCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedValueCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotTextCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertSelectedLabelCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertSelectedValueCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertTextCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertTitleCommand;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertValueCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\AssertFileDownloadedCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UpdateClipboardCommand;
use Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand;
use Tienvx\Bundle\MbtBundle\Command\Keyboard\SendKeysCommand;
use Tienvx\Bundle\MbtBundle\Command\Keyboard\TypeCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\AddSelectionCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\CheckCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\ClickAtCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\ClickCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\DoubleClickAtCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\DoubleClickCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\DragAndDropToObjectCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseDownAtCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseDownCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseMoveAtCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseOutCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseOverCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseUpAtCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseUpCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\RemoveSelectionCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\SelectCommand;
use Tienvx\Bundle\MbtBundle\Command\Mouse\UncheckCommand;
use Tienvx\Bundle\MbtBundle\Command\Script\ExecuteAsyncScriptCommand;
use Tienvx\Bundle\MbtBundle\Command\Script\ExecuteScriptCommand;
use Tienvx\Bundle\MbtBundle\Command\Script\RunScriptCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreAttributeCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreElementCountCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreJsonCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreTextCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreTitleCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreValueCommand;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreWindowHandleCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementEditableCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotEditableCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotPresentCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotVisibleCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementPresentCommand;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementVisibleCommand;
use Tienvx\Bundle\MbtBundle\Command\Window\CloseCommand;
use Tienvx\Bundle\MbtBundle\Command\Window\OpenCommand;
use Tienvx\Bundle\MbtBundle\Command\Window\SelectFrameCommand;
use Tienvx\Bundle\MbtBundle\Command\Window\SelectWindowCommand;
use Tienvx\Bundle\MbtBundle\Command\Window\SetWindowSizeCommand;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

interface CommandManagerInterface
{
    public const COMMANDS = [
        'acceptAlert' => AcceptAlertCommand::class,
        'acceptConfirmation' => AcceptAlertCommand::class,
        'answerPrompt' => AnswerPromptCommand::class,
        'dismissConfirmation' => DismissPromptCommand::class,
        'dismissPrompt' => DismissPromptCommand::class,
        'assert' => AssertCommand::class,
        'assertAlert' => AssertAlertCommand::class,
        'assertConfirmation' => AssertAlertCommand::class,
        'assertPrompt' => AssertAlertCommand::class,
        'assertTitle' => AssertTitleCommand::class,
        'assertText' => AssertTextCommand::class,
        'assertNotText' => AssertNotTextCommand::class,
        'assertValue' => AssertValueCommand::class,
        'assertEditable' => AssertEditableCommand::class,
        'assertNotEditable' => AssertNotEditableCommand::class,
        'assertElementPresent' => AssertElementPresentCommand::class,
        'assertElementNotPresent' => AssertElementNotPresentCommand::class,
        'assertChecked' => AssertCheckedCommand::class,
        'assertNotChecked' => AssertNotCheckedCommand::class,
        'assertSelectedValue' => AssertSelectedValueCommand::class,
        'assertNotSelectedValue' => AssertNotSelectedValueCommand::class,
        'assertSelectedLabel' => AssertSelectedLabelCommand::class,
        'assertNotSelectedLabel' => AssertNotSelectedLabelCommand::class,
        'upload' => UploadCommand::class,
        'assertFileDownloaded' => AssertFileDownloadedCommand::class,
        'assertClipboard' => AssertClipboardCommand::class,
        'updateClipboard' => UpdateClipboardCommand::class,
        'type' => TypeCommand::class,
        'sendKeys' => SendKeysCommand::class,
        'addSelection' => AddSelectionCommand::class,
        'removeSelection' => RemoveSelectionCommand::class,
        'check' => CheckCommand::class,
        'uncheck' => UncheckCommand::class,
        'click' => ClickCommand::class,
        'clickAt' => ClickAtCommand::class,
        'doubleClick' => DoubleClickCommand::class,
        'doubleClickAt' => DoubleClickAtCommand::class,
        'dragAndDropToObject' => DragAndDropToObjectCommand::class,
        'mouseDown' => MouseDownCommand::class,
        'mouseDownAt' => MouseDownAtCommand::class,
        'mouseMoveAt' => MouseMoveAtCommand::class,
        'mouseOut' => MouseOutCommand::class,
        'mouseOver' => MouseOverCommand::class,
        'mouseUp' => MouseUpCommand::class,
        'mouseUpAt' => MouseUpAtCommand::class,
        'select' => SelectCommand::class,
        'runScript' => RunScriptCommand::class,
        'executeScript' => ExecuteScriptCommand::class,
        'executeAsyncScript' => ExecuteAsyncScriptCommand::class,
        'store' => StoreCommand::class,
        'storeAttribute' => StoreAttributeCommand::class,
        'storeElementCount' => StoreElementCountCommand::class,
        'storeJson' => StoreJsonCommand::class,
        'storeText' => StoreTextCommand::class,
        'storeTitle' => StoreTitleCommand::class,
        'storeValue' => StoreValueCommand::class,
        'storeWindowHandle' => StoreWindowHandleCommand::class,
        'waitForElementEditable' => WaitForElementEditableCommand::class,
        'waitForElementNotEditable' => WaitForElementNotEditableCommand::class,
        'waitForElementPresent' => WaitForElementPresentCommand::class,
        'waitForElementNotPresent' => WaitForElementNotPresentCommand::class,
        'waitForElementVisible' => WaitForElementVisibleCommand::class,
        'waitForElementNotVisible' => WaitForElementNotVisibleCommand::class,
        'open' => OpenCommand::class,
        'setWindowSize' => SetWindowSizeCommand::class,
        'selectWindow' => SelectWindowCommand::class,
        'close' => CloseCommand::class,
        'selectFrame' => SelectFrameCommand::class,
    ];

    public function hasCommand(string $command): bool;

    public function isTargetMissing(string $command, ?string $target): bool;

    public function isTargetNotValid(string $command, ?string $target): bool;

    public function isValueMissing(string $command, ?string $value): bool;

    public function isValueNotValid(string $command, ?string $value): bool;

    public function run(
        string $command,
        ?string $target,
        ?string $value,
        ValuesInterface $values,
        RemoteWebDriver $driver
    ): void;
}
