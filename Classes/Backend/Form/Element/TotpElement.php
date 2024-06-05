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

use Causal\MfaFrontend\Domain\Immutable\TotpSecret;
use Causal\MfaFrontend\Domain\SecretFactory;
use Causal\MfaFrontend\Event\DisableTotpEvent;
use Causal\MfaFrontend\Traits\IssuerTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractGenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Fluid\View\StandaloneView;

if ((new Typo3Version())->getMajorVersion() >= 13) {
    abstract class ParentElementClass extends AbstractFormElement {
        public function __construct(
            protected readonly EventDispatcherInterface $eventDispatcher,
            protected readonly SecretFactory $secretFactory
        )
        {
        }
    }
} else {
    abstract class ParentElementClass extends AbstractFormElement {
        protected EventDispatcherInterface $eventDispatcher;
        protected SecretFactory $secretFactory;

        public function __construct(NodeFactory $nodeFactory, array $data)
        {
            parent::__construct($nodeFactory, $data);

            // Unfortunately DI cannot be used here, as the form element is instantiated
            // by the Core and "array" is not a valid type hint for the constructor
            $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
            $this->secretFactory = GeneralUtility::makeInstance(SecretFactory::class);
        }
    }
}

class TotpElement extends ParentElementClass
{
    use IssuerTrait;

    protected ?TotpSecret $totpSecret = null;

    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $templateView = $this->initializeTemplateView();
        $isEnabled = $this->isTotpEnabled();
        $tableName = $this->data['tableName'] ?? null;
        $recordUid = $this->data['databaseRow']['uid'] ?? null;

        if ($isEnabled && $tableName !== null && $recordUid !== null) {
            $bypassValidation = $GLOBALS['BE_USER']->isAdmin();
            $event = new DisableTotpEvent(
                $tableName,
                $recordUid,
                $bypassValidation
            );
            $this->eventDispatcher->dispatch($event);
            if ($event->getBypassValidation()) {
                // No need to provide a TOTP to disable
                $resultArray['html'] = '';
                return $resultArray;
            }
        }

        if ((new Typo3Version())->getMajorVersion() >= 12) {
            $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@causal/mfa-frontend/totp-element.js');
        } else {
            $resultArray['requireJsModules']['locationMap'] = [
                'TYPO3/CMS/MfaFrontend/Backend/TotpElement' => 'function(TotpElement) {}'
            ];
        }

        $prefix = '';
        if ($tableName !== null) {
            $prefix .= sprintf('[%s]', $tableName);
        }
        if ($recordUid !== null) {
            $prefix .= sprintf('[%s]', $recordUid);
        }

        $templateView->assignMultiple([
            'typo3Version' => (new Typo3Version())->getMajorVersion(),
            'prefix' => $prefix,
            'isEnabled' => $isEnabled,
            'totpSecret' => $this->getTotpSecret(),
        ]);

        $resultArray['html'] = $templateView->render();

        return $resultArray;
    }

    protected function initializeTemplateView(): StandaloneView
    {
        $resourcesPath = 'EXT:mfa_frontend/Resources/Private/';

        /** @var StandaloneView $templateView */
        $templateView = GeneralUtility::makeInstance(StandaloneView::class);
        $templateView->setLayoutRootPaths([$resourcesPath . 'Layouts/']);
        $templateView->setPartialRootPaths([$resourcesPath . 'Partials/']);
        //$templateView->setTemplateRootPaths([$resourcesPath . 'Templates/']);

        $templateView->setTemplatePathAndFilename(
            $resourcesPath . 'Templates/Backend/TotpElement.html'
        );

        return $templateView;
    }

    protected function getTotpSecret(): TotpSecret
    {
        if ($this->totpSecret === null) {
            $secretKey = null;
            if ($this->isTotpEnabled()) {
                $secretKey = (string)$this->data['databaseRow']['tx_mfafrontend_secret'];
            }

            $this->totpSecret = $this->secretFactory->create(
                $this->getIssuer($this->data['tableName']),
                $this->getUsername(),
                $secretKey
            );
        }

        return $this->totpSecret;
    }

    protected function getUsername(): string
    {
        // TODO: Add support for arbitrary field name?
        return $this->data['databaseRow']['username'] ?? '';
    }

    protected function isTotpEnabled(): bool
    {
        return (bool)$this->data['databaseRow']['tx_mfafrontend_enable'];
    }
}
