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

namespace Causal\MfaFrontend\Traits;

use TYPO3\CMS\Core\Authentication\Mfa\Provider\Totp;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait VerifyOtpTrait
{
    private function verifyOneTimePassword(string $secret, string $oneTimePassword): bool
    {
        $totp = GeneralUtility::makeInstance(
            Totp::class,
            $secret,
            'sha1'
        );

        return $totp->verifyTotp($oneTimePassword, 2);
    }
}
