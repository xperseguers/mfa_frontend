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

namespace Causal\MfaFrontend\Service;

use Causal\MfaFrontend\Traits\MfaFieldTrait;
use Causal\MfaFrontend\Traits\VerifyOtpTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MfaAuthenticationService extends AuthenticationService
{
    use MfaFieldTrait;
    use VerifyOtpTrait;

    public const FAIL_AND_STOP = 0;
    public const FAIL_AND_PROCEED = 100;
    public const AUTH_SUCCEED_AND_PROCEED = 70;

    public function authUser(array $user): int
    {
        $mfaField = static::getMfaField($this->db_user['table']);
        $mfa = json_decode($user[$mfaField] ?? '', true) ?? [];

        if (!($mfa['totp']['active'] ?? false)) {
            // Not responsible, check other services
            return static::FAIL_AND_PROCEED;
        }

        $secret = $mfa['totp']['secret'] ?? '';
        if ((new Typo3Version())->getMajorVersion() >= 12) {
            /** @var ServerRequestInterface $request */
            $request = $this->authInfo['request'];
            $otp = $request->getParsedBody()['mfa-frontend-otp'] ?? $request->getQueryParams()['mfa-frontend-otp'] ?? '';
        } else {
            $otp = GeneralUtility::_GP('mfa-frontend-otp') ?? '';
        }

        if ($this->verifyOneTimePassword($secret, $otp)) {
            // Store last usage of TOTP
            $mfa['totp']['lastUsed'] = $GLOBALS['EXEC_TIME'];
            // Reset failed attempts
            $mfa['totp']['attempts'] = 0;

            $code = static::AUTH_SUCCEED_AND_PROCEED;
        } else {
            // Increase failed attempts
            $mfa['totp']['attempts']++;

            $code = static::FAIL_AND_STOP;
        }

        // Store updated MFA configuration
        $mfa['totp']['updated'] = $GLOBALS['EXEC_TIME'];
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->db_user['table'])
            ->update(
                $this->db_user['table'],
                [
                    $mfaField => json_encode($mfa),
                ],
                [
                    'uid' => $user['uid'],
                ]
            );

        return $code;
    }
}
