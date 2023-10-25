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

namespace Causal\MfaFrontend\Domain\Model;

use Causal\MfaFrontend\Traits\MfaFieldTrait;

class TotpSettings
{
    use MfaFieldTrait;

    protected array $mfa = [];

    protected bool $enabled = false;

    protected string $secret = '';

    public static function createFromRecord(array $record, string $table): self
    {
        $mfaField = static::getMfaField($table);
        $mfa = json_decode($record[$mfaField] ?? '', true) ?? [];

        return (new self())
            ->setMfa($mfa)
            ->setEnabled($mfa['totp']['active'] ?? false)
            ->setSecret($mfa['totp']['secret'] ?? '');
    }

    public static function createFromVirtualData(array $data): self
    {
        return (new self())
            ->setEnabled((bool)($data['tx_mfafrontend_enable'] ?? false))
            ->setSecret((string)($data['tx_mfafrontend_secret'] ?? ''));
    }

    public function getMfa(): array
    {
        return $this->mfa;
    }

    /**
     * @internal
     */
    public function setMfa(array $mfa): self
    {
        $this->mfa = $mfa;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    public function toArray(string $table): array
    {
        $mfaField = static::getMfaField($table);
        $mfa = $this->getMfa();

        if (isset($mfa['totp'])) {
            $currentSecret = $mfa['totp']['secret'] ?? '';
            $currentEnabled = $mfa['totp']['active'] ?? false;
            if ($currentSecret === $this->getSecret()
                && $currentEnabled === $this->isEnabled()) {
                // Nothing changed
                return [
                    $mfaField => json_encode($mfa),
                ];
            }
        }

        $mfa['totp'] = [
            'secret' => $this->getSecret(),
            'active' => $this->isEnabled(),
            'created' => $GLOBALS['EXEC_TIME'],
            'updated' => $GLOBALS['EXEC_TIME'],
            'attempts' => 0,
            'lastUsed' => 0,
        ];

        return [
            $mfaField => json_encode($mfa),
        ];
    }
}
