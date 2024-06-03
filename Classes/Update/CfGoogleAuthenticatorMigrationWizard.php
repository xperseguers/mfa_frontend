<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\MfaFrontend\Update;

use Causal\MfaFrontend\Event\CollectAllowedTablesEvent;
use Causal\MfaFrontend\Traits\MfaFieldTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * @deprecated since version 1.2.0, will be removed in 1.3.0.
 */
class CfGoogleAuthenticatorMigrationWizard implements UpgradeWizardInterface
{
    use MfaFieldTrait;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getTitle(): string
    {
        return 'Migrate TOTP settings from cf_google_authenticator';
    }

    public function getDescription(): string
    {
        return 'Migrates TOTP configuration from EXT:cf_google_authenticator to native'
            . ' Core format and the format used by this extension.';
    }

    public function getIdentifier(): string
    {
        return 'CfGoogleAuthenticatorMigrationWizard';
    }

    public function updateNecessary(): bool
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $targetField = static::getMfaField($table);
            if (!$this->canMigrateTable($table, $targetField)) {
                continue;
            }

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            if ($queryBuilder
                ->count('*')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('tx_cfgoogleauthenticator_enabled', 1),
                    $queryBuilder->expr()->like(
                        'tx_cfgoogleauthenticator_secret',
                        $queryBuilder->quote('%')
                    )
                )
                ->execute()
                ->fetchOne() > 0
            ) {
                return true;
            }
        }

        return false;
    }

    public function executeUpdate(): bool
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $targetField = static::getMfaField($table);
            if (!$this->canMigrateTable($table, $targetField)) {
                continue;
            }

            $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($table);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            $statement = $queryBuilder
                ->select('uid', 'tx_cfgoogleauthenticator_secret', 'mfa')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('tx_cfgoogleauthenticator_enabled', 1),
                    $queryBuilder->expr()->like(
                        'tx_cfgoogleauthenticator_secret',
                        $queryBuilder->quote('%')
                    )
                )
                ->execute();

            while (($row = $statement->fetchAssociative()) !== false) {
                $existingMfa = json_decode($row[$targetField] ?? '', true) ?? [];
                $data = [
                    'tx_cfgoogleauthenticator_enabled' => 0,
                    'tx_cfgoogleauthenticator_secret' => null,
                ];
                if (!($existingMfa['totp']['active'] ?? false)) {
                    $mfa = [
                        'totp' => [
                            'secret' => $row['tx_cfgoogleauthenticator_secret'],
                            'active' => true,
                            'created' => $GLOBALS['EXEC_TIME'],
                            'updated' => $GLOBALS['EXEC_TIME'],
                            'attempts' => 0,
                            'lastUsed' => 0,
                        ]
                    ];
                    $data[$targetField] = json_encode($mfa);
                }
                $tableConnection->update(
                    $table,
                    $data,
                    [
                        'uid' => $row['uid']
                    ]
                );
            }
        }

        return true;
    }

    protected function getTables(): array
    {
        $event = new CollectAllowedTablesEvent([
            'be_users',
            'fe_users',
        ]);
        $this->eventDispatcher->dispatch($event);

        return $event->getTables();
    }

    protected function canMigrateTable(string $table, string $targetField): bool
    {
        $requiredColumns = [
            'tx_cfgoogleauthenticator_secret',
            'tx_cfgoogleauthenticator_enabled',
            $targetField,
        ];
        $columns = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->getSchemaManager()
            ->listTableColumns($table);

        foreach ($requiredColumns as $requiredColumn) {
            if (!isset($columns[$requiredColumn])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string[] All new fields and tables must exist
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}
