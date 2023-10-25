<?php
defined('TYPO3') || die();

// Register Frontend plugins
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'mfa_frontend',
    'Setup',
    'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:plugin.setup'
);

$pluginSignature = 'mfafrontend_setup';
// Disable the display of page fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages,recursive';
