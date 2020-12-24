<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;

class ValidSeleniumConfigValidator extends ConstraintValidator
{
    protected ProviderManager $providerManager;

    public function __construct(ProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidSeleniumConfig) {
            throw new UnexpectedTypeException($constraint, ValidSeleniumConfig::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof SeleniumConfigInterface) {
            throw new UnexpectedValueException($value, SeleniumConfigInterface::class);
        }

        if (
            !in_array($value->getProvider(), $this->providerManager->getProviders())
            || !in_array($value->getPlatform(), $this->providerManager->getPlatforms($value->getProvider()))
            || !in_array(
                $value->getBrowser(),
                $this->providerManager->getBrowsers($value->getProvider(), $value->getPlatform())
            )
            || !in_array(
                $value->getBrowserVersion(),
                $this->providerManager->getBrowserVersions(
                    $value->getProvider(),
                    $value->getPlatform(),
                    $value->getBrowser()
                )
            )
            || !in_array(
                $value->getResolution(),
                $this->providerManager->getResolutions($value->getProvider(), $value->getPlatform())
            )
        ) {
            $this->context->buildViolation($constraint->message)
                ->setCode(ValidSeleniumConfig::IS_SELENIUM_CONFIG_INVALID_ERROR)
                ->addViolation();
        }
    }
}
