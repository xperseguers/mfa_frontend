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

class TotpSettings
{
    protected bool $enabled = false;

    protected string $secret = '';

    public static function createFromRecord(array $record): self
    {
        return (new self())
            ->setEnabled((bool)$record['tx_mfafrontend_enable'])
            ->setSecret((string)$record['tx_mfafrontend_secret']);
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

    public function toArray(): array
    {
        return [
            'tx_mfafrontend_enable' => $this->isEnabled() ? 1 : 0,
            'tx_mfafrontend_secret' => $this->getSecret(),
        ];
    }
}
