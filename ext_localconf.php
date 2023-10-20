<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
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
