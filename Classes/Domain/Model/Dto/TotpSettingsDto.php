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

namespace Causal\MfaFrontend\Domain\Model\Dto;

use Causal\MfaFrontend\Domain\Model\TotpSettings;

class TotpSettingsDto
{
    protected ?TotpSettings $oldSettings = null;

    protected ?TotpSettings $newSettings = null;

    protected string $oneTimePassword = '';

    public function getOldSettings(): TotpSettings
    {
        if ($this->oldSettings === null) {
            throw new \RuntimeException('Cannot get old settings before they have been set', 1697785678);
        }
        return $this->oldSettings;
    }

    public function setOldSettings(TotpSettings $oldSettings): self
    {
        $this->oldSettings = $oldSettings;
        return $this;
    }

    public function getNewSettings(): TotpSettings
    {
        if ($this->newSettings === null) {
            throw new \RuntimeException('Cannot get new settings before they have been set', 1697785679);
        }
        return $this->newSettings;
    }

    public function setNewSettings(TotpSettings $newSettings): self
    {
        $this->newSettings = $newSettings;
        return $this;
    }

    public function getOneTimePassword(): string
    {
        return $this->oneTimePassword;
    }

    public function setOneTimePassword(string $oneTimePassword): self
    {
        $this->oneTimePassword = $oneTimePassword;
        return $this;
    }
}
