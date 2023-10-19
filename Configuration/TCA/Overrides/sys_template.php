<?php
defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'mfa_frontend',
    'Configuration/TypoScript',
    'MFA Frontend'
);
