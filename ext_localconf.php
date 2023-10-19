<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1697740814] = [
        'nodeName' => 'MfaFrontendTotp',
        'priority' => 40,
        'class' => \Causal\MfaFrontend\Form\Element\TotpElement::class,
    ];
})('mfa_frontend');
