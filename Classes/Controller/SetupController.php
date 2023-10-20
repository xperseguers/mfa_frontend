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

namespace Causal\MfaFrontend\Controller;

use Causal\MfaFrontend\Domain\Form\SetupForm;
use Causal\MfaFrontend\Domain\Immutable\TotpSecret;
use Causal\MfaFrontend\Domain\Repository\FrontendUserRepository;
use Causal\MfaFrontend\Domain\SecretFactory;
use Causal\MfaFrontend\Trait\IssuerTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

/**
 * Two-factor authentication setup controller.
 *
 * A controller that allows Frontend users to set up the
 * two-factor authentication for their respective account.
 */
class SetupController extends ActionController
{
    use IssuerTrait;

    protected FrontendUserRepository $frontendUserRepository;

    protected Context $context;

    protected SecretFactory $secretFactory;

    protected ?TotpSecret $totpSecret = null;

    public function __construct(
        FrontendUserRepository $frontendUserRepository,
        Context $context,
        SecretFactory $secretFactory
    )
    {
        $this->frontendUserRepository = $frontendUserRepository;
        $this->context = $context;
        $this->secretFactory = $secretFactory;
    }

    public function indexAction(): ResponseInterface
    {
        if ($this->isUserLoggedIn()) {
            $totpSecret = $this->getTotpSecret();
            $isEnabled = $this->isTotpEnabled();
            $setupForm = $this->getSetupForm();

            $this->view->assignMultiple(
                [
                    'isEnabled' => $isEnabled,
                    'formData' => $setupForm,
                    'formName' => SetupForm::FORM_NAME,
                    'totpSecret' => $totpSecret,
                ]
            );
        }

        return $this->htmlResponse($this->view->render());
    }

    protected function isUserLoggedIn(): bool
    {
        return (bool)$this->context->getPropertyFromAspect(
            'frontend.user',
            'isLoggedIn'
        );
    }

    protected function getTotpSecret(): TotpSecret
    {
        if ($this->totpSecret === null) {
            $secretKey = null;
            if ($this->isTotpEnabled()) {
                $mfa = $this->getUserMfa();
                $secretKey = $mfa['totp']['secret'] ?? '';
            }

            $this->totpSecret = $this->secretFactory->create(
                $this->getIssuer('fe_users'),
                $this->getFrontendUser()['username'],
                $secretKey
            );
        }

        return $this->totpSecret;
    }

    protected function isValidUpdateRequest(): bool
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if (version_compare($typo3Version->getBranch(), '12.0', '>=')) {
            $extbaseRequestParameters = clone $this->request->getAttribute('extbase');
            $result = $extbaseRequestParameters->getOriginalRequestMappingResults();
        } else {
            // TYPO3 v11
            $result = $this->request->getOriginalRequestMappingResults();
        }

        $hasErrors = $result->hasErrors();
        $hasArgument = $this->request->hasArgument(SetupForm::FORM_NAME);

        return !$hasErrors && $hasArgument;
    }

    protected function isTotpEnabled(): bool
    {
        $mfa = $this->getUserMfa();
        return $mfa['totp']['active'] ?? false;
    }

    protected function getUserMfa(): array
    {
        return json_decode($this->getFrontendUser()['mfa_frontend'] ?? '', true) ?? [];
    }

    protected function getFrontendUser(): array
    {
        return $this->request->getAttribute('frontend.user')->user;
    }

    protected function getSetupForm(): SetupForm
    {
        return GeneralUtility::makeInstance(
            SetupForm::class,
            $this->getTotpSecret()->getSecretKey(),
            ''
        );
    }

    protected function getFormObject(): SetupForm
    {
        $formData = (array)$this->request->getArgument(SetupForm::FORM_NAME);

        return GeneralUtility::makeInstance(
            SetupForm::class,
            $formData['secret'],
            $formData['oneTimePassword']
        );
    }
}
