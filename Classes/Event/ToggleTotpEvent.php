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

use Causal\MfaFrontend\Domain\Model\FrontendUser;

final class ToggleTotpEvent
{
    private ?string $action;

    private FrontendUser $user;

    public function __construct(
        ?string $action,
        FrontendUser $user
    )
    {
        $this->action = $action;
        $this->user = $user;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getUser(): FrontendUser
    {
        return $this->user;
    }
}
