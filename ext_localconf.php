<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    // Register hooks into \TYPO3\CMS\Core\DataHandling\DataHandler
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
        = \Causal\MfaFrontend\Hook\DataHandler::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1697740814] = [
        'nodeName' => 'MfaFrontendTotp',
        'priority' => 40,
        'class' => \Causal\MfaFrontend\Form\Element\TotpElement::class,
    ];
})('mfa_frontend');
