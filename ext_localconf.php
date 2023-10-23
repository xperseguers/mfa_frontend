<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);

    // Service configuration
    $subtype = 'authUserFE';
    $description = 'Enable Google 2FA for Frontend login';
    if (version_compare($typo3Version->getBranch(), '10.4', '=')) {
        $subtype = 'authUserFE,authUserBE';
        $description = 'Enable Google 2FA for Frontend and Backend login';

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1433416747]['provider']
            = \Causal\MfaFrontend\Backend\LoginProvider\TotpLoginProvider::class;
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        $_EXTKEY,
        'auth',
        \Causal\MfaFrontend\Service\MfaAuthenticationService::class,
        [
            'title' => 'Google Authenticator',
            'description' => $description,
            'subtype' => $subtype,
            'available' => true,
            'priority' => 80,
            'quality' => 80,
            'os' => '',
            'exec' => '',
            'className' => \Causal\MfaFrontend\Service\MfaAuthenticationService::class,
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Setup',
        [
            \Causal\MfaFrontend\Controller\SetupController::class => 'index,update',
        ],
        [
            \Causal\MfaFrontend\Controller\SetupController::class => 'index,update',
        ]
    );

    // Register hooks into \TYPO3\CMS\Core\DataHandling\DataHandler
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
        = \Causal\MfaFrontend\Hook\DataHandler::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1697740814] = [
        'nodeName' => 'MfaFrontendTotp',
        'priority' => 40,
        'class' => \Causal\MfaFrontend\Form\Element\TotpElement::class,
    ];

    // Migrate TOTP setup from EXT:cf_google_authenticator
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['CfGoogleAuthenticatorMigrationWizard']
        = \Causal\MfaFrontend\Update\CfGoogleAuthenticatorMigrationWizard::class;

    if (version_compare($typo3Version->getBranch(), '10.4', '=')) {
        if (!class_exists('Base32\\Base32')) {
            include_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/CompatibilityV10/Base32.php';
        }
        include_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/CompatibilityV10/Totp.php';
    }
})('mfa_frontend');
