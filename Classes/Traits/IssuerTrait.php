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

use Causal\MfaFrontend\Event\DefineIssuerLayerEvent;

trait IssuerTrait
{
    protected function getIssuer(string $table): string
    {
        return vsprintf(
            '%s - %s',
            [
                $this->getSiteName(),
                $this->getLayer($table),
            ]
        );
    }

    private function getSiteName(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
    }

    protected function getLayer(string $table): string
    {
        $event = new DefineIssuerLayerEvent(
            $table,
            $table === 'fe_users' ? 'Frontend' : ''
        );
        $this->eventDispatcher->dispatch($event);

        return $event->getLayer();
    }
}
