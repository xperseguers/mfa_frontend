<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    // Service configuration
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        $_EXTKEY,
        'auth',
        \Causal\MfaFrontend\Service\MfaAuthenticationService::class,
        [
            'title' => 'Google Authenticator',
            'description' => 'Enable Google 2FA for frontend login',
            'subtype' => 'authUserFE',
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
})('mfa_frontend');
