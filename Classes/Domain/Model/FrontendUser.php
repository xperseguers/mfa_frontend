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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class FrontendUser extends AbstractEntity
{
    protected string $username;

    protected string $mfaFrontend = '';

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    protected function getMfa(): array
    {
        return json_decode($this->mfaFrontend, true) ?? [];
    }

    protected function setMfa(array $mfa): self
    {
        $this->mfaFrontend = json_encode($mfa);
        return $this;
    }

    /**
     * @return string
     * @api Useful for 3rd-party extensions in need to keep that in sync with their own domain model
     */
    public function getRawMfa(): string
    {
        return $this->mfaFrontend;
    }

    public function enableOneTimePassword(string $secret): self
    {
        $mfa = $this->getMfa();
        $mfa['totp'] = [
            'secret' => $secret,
            'active' => true,
            'created' => $GLOBALS['EXEC_TIME'],
            'updated' => $GLOBALS['EXEC_TIME'],
            'attempts' => 0,
            'lastUsed' => 0,
        ];
        $this->setMfa($mfa);
        return $this;
    }

    public function disableOneTimePassword(): self
    {
        $mfa = $this->getMfa();
        unset($mfa['totp']);
        $this->setMfa($mfa);
        return $this;
    }
}
