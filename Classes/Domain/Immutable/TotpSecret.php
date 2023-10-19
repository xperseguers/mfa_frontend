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

namespace Causal\MfaFrontend\Domain\Immutable;

final class TotpSecret
{
    public const BASE_URL = 'otpauth://totp/';

    protected string $issuer;

    protected string $accountName;

    private string $secretKey;

    private ?string $uri = null;

    /**
     * @param string $issuer
     * @param string $accountName
     * @param string $secretKey
     */
    public function __construct(string $issuer, string $accountName, string $secretKey)
    {
        if (strpos($issuer . $accountName, ':') !== false) {
            throw new \InvalidArgumentException(
                'Neither the \'issuer\' parameter nor the \'accountName\' parameter may contain a colon.'
            );
        }

        $this->issuer = $issuer;
        $this->accountName = $accountName;
        $this->secretKey = $secretKey;
    }

    public function getUri(): string
    {
        if ($this->uri === null) {
            $params = [
                'secret' => $this->getSecretKey(),
                'issuer' => rawurlencode($this->getIssuer()),
            ];

            $query = http_build_query($params);
            $queryDecoded = rawurldecode($query);

            $this->uri = vsprintf(
                '%s%s?%s',
                [
                    self::BASE_URL,
                    rawurlencode($this->getLabel()),
                    $queryDecoded,
                ]
            );
        }

        return $this->uri;
    }

    public function getLabel(): string
    {
        return vsprintf(
            '%s:%s',
            [
                $this->getIssuer(),
                $this->getAccountName(),
            ]
        );
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}
