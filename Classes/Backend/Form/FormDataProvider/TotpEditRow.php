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

namespace Causal\MfaFrontend\Backend\Form\FormDataProvider;

use Causal\MfaFrontend\Event\CollectAllowedTablesEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractDatabaseRecordProvider;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class TotpEditRow
    extends AbstractDatabaseRecordProvider
    implements FormDataProviderInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Injects the virtual configuration.
     *
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
        $event = new CollectAllowedTablesEvent([
            'fe_users',
        ]);
        $this->eventDispatcher->dispatch($event);

        if (in_array($result['tableName'], $event->getTables(), true)) {
            $row = $result['databaseRow'];

            $mfaField = $result['tableName'] === 'fe_users' ? 'mfa_frontend' : 'mfa';
            $mfa = json_decode($row[$mfaField] ?? '', true) ?? [];

            $row['tx_mfafrontend_enable'] = ($mfa['totp']['active'] ?? false) ? 1 : 0;
            $row['tx_mfafrontend_secret'] = $mfa['totp']['secret'] ?? '';

            $result['databaseRow'] = $row;
        }

        return $result;
    }
}
