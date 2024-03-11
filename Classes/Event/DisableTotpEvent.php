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

namespace Causal\MfaFrontend\Event;

final class DisableTotpEvent
{
    private string $table;

    private int $uid;

    private bool $bypassValidation;

    public function __construct(string $table, int $uid, bool $bypassValidation)
    {
        $this->table = $table;
        $this->uid = $uid;
        $this->bypassValidation = $bypassValidation;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getBypassValidation(): bool
    {
        return $this->bypassValidation;
    }

    public function setBypassValidation(bool $bypassValidation): self
    {
        $this->bypassValidation = $bypassValidation;
        return $this;
    }
}
