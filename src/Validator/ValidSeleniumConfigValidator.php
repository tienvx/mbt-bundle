<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\SeleniumConfigInterface;
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

        $valid = false;
        if ($this->providerManager->has($value->getProvider())) {
            $provider = $this->providerManager->get($value->getProvider());
            $valid = in_array($value->getPlatform(), $provider->getPlatforms())
                && in_array($value->getBrowser(), $provider->getBrowsers($value->getPlatform()))
                && in_array(
                    $value->getBrowserVersion(),
                    $provider->getBrowserVersions($value->getPlatform(), $value->getBrowser())
                )
                && in_array($value->getResolution(), $provider->getResolutions($value->getPlatform()));
        }
        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setCode(ValidSeleniumConfig::IS_SELENIUM_CONFIG_INVALID_ERROR)
                ->addViolation();
        }
    }
}
