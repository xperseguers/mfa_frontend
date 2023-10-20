<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\MfaFrontend\Validation\Validator;

use Causal\MfaFrontend\Domain\Form\SetupForm;
use Causal\MfaFrontend\Trait\VerifyOtpTrait;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractGenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

$typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
if (version_compare($typo3Version->getBranch(), '12.0', '>=')) {
    class ParentValidatorClass extends AbstractGenericObjectValidator {}
} else {
    class ParentValidatorClass extends GenericObjectValidator {}
}

class SetupFormValidator extends ParentValidatorClass
{
    use VerifyOtpTrait;

    public function canValidate(mixed $object): bool
    {
        parent::canValidate($object);

        return $object instanceof SetupForm;
    }

    protected function isValid(mixed $object): void
    {
        parent::isValid($object);

        /** @var SetupForm $object */
        $secret = $object->getSecret();
        $oneTimePassword = $object->getOneTimePassword();

        $isValid = $this->verifyOneTimePassword($secret, $oneTimePassword);

        if ($isValid !== true) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.otp.notvalid',
                    'mfa_frontend'
                ),
                1697810072
            );
        }
    }
}
