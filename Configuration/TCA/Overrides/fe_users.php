<?php
defined('TYPO3') || die();

$tempColumns = [
    'tx_mfafrontend_enable' => [
        'exclude' => false,
        'label' => 'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:fe_users.tx_mfafrontend_enable',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
        ],
    ],
    'tx_mfafrontend_secret' => [
        'exclude' => false,
        'label' => 'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:fe_users.tx_mfafrontend_secret',
        'config' => [
            'type' => 'user',
            'renderType' => 'MfaFrontendTotp',
        ],
    ],
    'mfa' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:mfa_auth,
        tx_mfafrontend_enable,
        tx_mfafrontend_secret'
);
