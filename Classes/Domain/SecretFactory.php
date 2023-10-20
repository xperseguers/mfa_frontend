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

namespace Causal\MfaFrontend\Domain;

use Causal\MfaFrontend\Domain\Immutable\TotpSecret;

class SecretFactory
{
    protected int $secretLength;

    protected const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * @param int $secretLength Length of the encoded string, as such, it must be divisible by 8
     */
    public function __construct(int $secretLength = 16)
    {
        if ($secretLength === 0 || $secretLength % 8 > 0) {
            throw new \InvalidArgumentException(
                'Secret length must be longer than 0 and divisible by 8',
                1697746216
            );
        }
        $this->secretLength = $secretLength;
    }

    /**
     * The specification technically allows you to only have an accountName not an issuer,
     * but as it's strongly recommended, we don't feel particularly guilty about forcing it
     * in the @see create() method.
     *
     * @param string $issuer
     * @param string $accountName
     * @return TotpSecret
     */
    public function create(string $issuer, string $accountName, ?string $secretKey = null): TotpSecret
    {
        if ($secretKey === null) {
            $secretKey = $this->generateSecretKey();
        }
        return new TotpSecret($issuer, $accountName, $secretKey);
    }

    /**
     * Generates a secret key.
     *
     * @return string
     */
    public function generateSecretKey(): string
    {
        $key = '';

        for ($i = 0; $i < $this->secretLength; $i++) {
            $key .= static::BASE32_CHARS[random_int(0, 31)];
        }

        return $key;
    }
}
