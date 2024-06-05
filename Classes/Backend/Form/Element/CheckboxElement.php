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

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Element\CheckboxToggleElement;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CheckboxElement extends AbstractFormElement
{
    public function render(): array
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        $resultArray = $this->initializeResultArray();
        $itePA = $this->data['parameterArray'];

        $value = $this->isTotpEnabled() ? 1 : 0;
        $itePA['itemFormElValue'] = $value;

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
