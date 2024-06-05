<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\MfaFrontend\Backend\Form\Element;

use Causal\MfaFrontend\Domain\SecretFactory;
use Causal\MfaFrontend\Event\DisableTotpEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Element\CheckboxToggleElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if ((new Typo3Version())->getMajorVersion() >= 13) {
    abstract class ParentCheckboxElementClass extends AbstractFormElement {
        public function __construct(
            protected readonly EventDispatcherInterface $eventDispatcher
        )
        {
        }
    }
} else {
    abstract class ParentCheckboxElementClass extends AbstractFormElement {
        protected EventDispatcherInterface $eventDispatcher;

        public function __construct(NodeFactory $nodeFactory, array $data)
        {
            parent::__construct($nodeFactory, $data);

            // Unfortunately DI cannot be used here, as the form element is instantiated
            // by the Core and "array" is not a valid type hint for the constructor
            $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        }
    }
}

class CheckboxElement extends ParentCheckboxElementClass
{
    public function render(): array
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        $resultArray = $this->initializeResultArray();
        $itePA = $this->data['parameterArray'];

        $value = $this->isTotpEnabled() ? 1 : 0;
        $itePA['itemFormElValue'] = $value;

        if ($typo3Version === 12) {
            // TYPO3 v12 only: the label is displayed twice without that flag
            $resultArray['labelHasBeenHandled'] = true;
        }

        if ($value === 1 && empty($itePA['fieldConf']['description'])) {
            $bypassValidation = $GLOBALS['BE_USER']->isAdmin();
            $event = new DisableTotpEvent(
                $this->data['tableName'],
                $this->data['databaseRow']['uid'],
                $bypassValidation
            );
            $this->eventDispatcher->dispatch($event);
            if ($event->getBypassValidation()) {
                $labelKey = 'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:fe_users.tx_mfafrontend_enable.descriptionAdmin';
                $itePA['fieldConf']['description'] = $GLOBALS['LANG']->sL($labelKey);
            }
        }

        $itePA['fieldConf']['config'] = [
            'items' => $typo3Version >= 12
                ? [
                    [
                        'label' => '',
                        'value' => '',
                    ],
                ]
                : [
                    ['', ''],
                ],
        ];

        $data = [
            'tableName' => $this->data['tableName'],
            'databaseRow' => $this->data['databaseRow'],
            'fieldName' => $this->data['fieldName'],
            'parameterArray' => $itePA,
            'processedTca' => $this->data['processedTca'],
        ];
        if ($typo3Version >= 13) {
            $toggleElement = GeneralUtility::makeInstance(CheckboxToggleElement::class);
            $toggleElement->setData($data);
        } else {
            $toggleElement = GeneralUtility::makeInstance(CheckboxToggleElement::class, $this->nodeFactory, $data);
        }
        $result = $toggleElement->render();

        $out = [];
        $out[] = $result['html'];

        $resultArray['html'] = implode('', $out);

        return $resultArray;
    }

    protected function isTotpEnabled(): bool
    {
        return (bool)$this->data['databaseRow']['tx_mfafrontend_enable'];
    }
}
