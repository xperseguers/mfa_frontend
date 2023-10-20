<?php
defined('TYPO3') || die();

(static function (string $_EXTKEY) {
    // Extend the record with virtual fields when editing
    $dataProviders =& $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'];
    $dataProviders[\Causal\MfaFrontend\Backend\Form\FormDataProvider\TotpEditRow::class] = [
        'after' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseEditRow::class,
        ]
    ];
})('mfa_frontend');
