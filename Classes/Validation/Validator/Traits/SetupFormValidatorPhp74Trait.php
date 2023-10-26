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

namespace Causal\MfaFrontend\Validation\Validator\Traits;

use Causal\MfaFrontend\Domain\Form\SetupForm;
use Causal\MfaFrontend\Traits\VerifyOtpTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait SetupFormValidatorPhp74Trait
{
    use VerifyOtpTrait;

    public function canValidate($object): bool
    {
        parent::canValidate($object);

        return $object instanceof SetupForm;
    }

    protected function isValid($object): void
    {
        parent::isValid($object);

        /** @var SetupForm $object */
        $secret = $object->getSecret();
        $oneTimePassword = $object->getOneTimePassword();
        $checksum = $object->getChecksum();

        if ($secret === '' || !hash_equals(GeneralUtility::hmac($secret, 'totp-setup'), $checksum)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.secret.tampered',
                    'mfa_frontend'
                ),
                1698271967
            );
            return;
        }

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
