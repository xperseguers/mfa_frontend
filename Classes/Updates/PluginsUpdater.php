<?php

declare(strict_types = 1);

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

namespace Causal\MfaFrontend\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class PluginsUpdater implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'TxMfaFrontendPlugins';
    }

    public function getTitle(): string
    {
        return 'EXT:mfa_frontend: Migrate plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates the mfa_frontend plugins to a dedicated CType.';
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getTtContentQueryBuilder();
        $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $rows = $queryBuilder
            ->select('*')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $tableConnection->update(
                'tt_content',
                [
                    'CType' => $row['list_type'],
                    'list_type' => '',
                ],
                [
                    'uid' => $row['uid'],
                ]
            );
        }

        $queryBuilder = $this->getBeGroupsQueryBuilder();
        $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_groups');

        $rows = $queryBuilder
            ->select('*')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $explicityAllowDeny = str_replace(
                [
                    'tt_content:list_type:mfafrontend_setup:ALLOW',
                    'tt_content:list_type:mfafrontend_setup:DENY',
                ],
                [
                    'tt_content:CType:mfafrontend_setup:ALLOW',
                    'tt_content:CType:mfafrontend_setup:DENY',
                ],
                $row['explicit_allowdeny']
            );
            $tableConnection->update(
                'be_groups',
                [
                    'explicit_allowdeny' => $explicityAllowDeny,
                ],
                [
                    'uid' => $row['uid'],
                ]
            );
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $ttContentQueryBuilder = $this->getTtContentQueryBuilder();
        $beGroupsQueryBuilder = $this->getBeGroupsQueryBuilder();

        $recordsToUpdate = $ttContentQueryBuilder
            ->count('*')
            ->executeQuery()
            ->fetchOne();
        $recordsToUpdate += $beGroupsQueryBuilder
            ->count('*')
            ->executeQuery()
            ->fetchOne();

        return $recordsToUpdate > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    protected function getTtContentQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $queryBuilder
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->quote('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->quote('mfafrontend_setup'))
            );

        return $queryBuilder;
    }

    protected function getBeGroupsQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_groups');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $queryBuilder
            ->from('be_groups')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->like('explicit_allowdeny', $queryBuilder->quote('%tt_content:list_type:mfafrontend_setup:ALLOW%')),
                    $queryBuilder->expr()->like('explicit_allowdeny', $queryBuilder->quote('%tt_content:list_type:mfafrontend_setup:DENY%'))
                )
            );

        return $queryBuilder;
    }
}
