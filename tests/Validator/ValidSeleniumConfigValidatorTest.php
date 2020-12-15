<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Provider\Selenoid;
use Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfig;
use Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfigValidator;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfigValidator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig
 * @covers \Tienvx\Bundle\MbtBundle\Provider\AbstractProvider
 * @covers \Tienvx\Bundle\MbtBundle\Provider\Selenoid
 */
class ValidSeleniumConfigValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var ProviderManager|MockObject
     */
    protected ProviderManager $providerManager;

    protected function createValidator()
    {
        $this->providerManager = $this->createMock(ProviderManager::class);
        return new ValidSeleniumConfigValidator($this->providerManager);
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        if ($value) {
            $this->providerManager->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
            $this->providerManager->expects($this->once())->method('get')->with('selenoid')->willReturn(new Selenoid());
        }
        $this->validator->validate($value, new ValidSeleniumConfig());

        $this->assertNoViolation();
    }

    public function getValidValues(): array
    {
        $validSeleniumConfig = new SeleniumConfig();
        $validSeleniumConfig->setProvider('selenoid');
        $validSeleniumConfig->setPlatform(WebDriverPlatform::LINUX);
        $validSeleniumConfig->setBrowser(WebDriverBrowserType::FIREFOX);
        $validSeleniumConfig->setBrowserVersion('48.0');
        $validSeleniumConfig->setResolution('1024x768');
        return [
            [null],
            [$validSeleniumConfig],
        ];
    }

    public function testInvalidProvider()
    {
        $this->providerManager->expects($this->once())->method('has')->with('invalid')->willReturn(false);
        $this->providerManager->expects($this->never())->method('get')->with('invalid');
        $constraint = new ValidSeleniumConfig([
            'message' => 'myMessage',
        ]);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('invalid');

        $this->validator->validate($seleniumConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidSeleniumConfig::IS_SELENIUM_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testInvalidSeleniumConfigForSelenoid()
    {
        $this->providerManager->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->providerManager->expects($this->once())->method('get')->with('selenoid')->willReturn(new Selenoid());
        $constraint = new ValidSeleniumConfig([
            'message' => 'myMessage',
        ]);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('selenoid');
        $seleniumConfig->setPlatform(WebDriverPlatform::WINDOWS);
        $seleniumConfig->setBrowser(WebDriverBrowserType::SAFARI);
        $seleniumConfig->setBrowserVersion('99.9');
        $seleniumConfig->setResolution('123x999');

        $this->validator->validate($seleniumConfig, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(ValidSeleniumConfig::IS_SELENIUM_CONFIG_INVALID_ERROR)
            ->assertRaised();
    }

    public function testUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            ValidSeleniumConfig::class,
            Email::class
        ));
        $this->validator->validate('test@example.com', new Email());
    }

    public function testUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            SeleniumConfigInterface::class,
            'stdClass'
        ));
        $this->validator->validate(new \stdClass(), new ValidSeleniumConfig());
    }
}
