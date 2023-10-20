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
use Causal\MfaFrontend\Domain\Model\FrontendUser;
use Causal\MfaFrontend\Domain\Repository\FrontendUserRepository;
use Causal\MfaFrontend\Domain\SecretFactory;
use Causal\MfaFrontend\Event\ToggleTotpEvent;
use Causal\MfaFrontend\Trait\IssuerTrait;
use Causal\MfaFrontend\Validation\Validator\SetupFormValidator;
use CodeFareith\CfGoogleAuthenticator\Utility\PathUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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

    protected SetupFormValidator $setupFormValidator;

    protected SecretFactory $secretFactory;

    protected Context $context;

    protected ?TotpSecret $totpSecret = null;

    public function __construct(
        FrontendUserRepository $frontendUserRepository,
        SetupFormValidator $setupFormValidator,
        SecretFactory $secretFactory,
        Context $context
    )
    {
        $this->frontendUserRepository = $frontendUserRepository;
        $this->setupFormValidator = $setupFormValidator;
        $this->secretFactory = $secretFactory;
        $this->context = $context;
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

    public function updateAction(): ResponseInterface
    {
        if (($response = $this->validateUpdateRequest()) instanceof ResponseInterface) {
            return $response;
        }

        $user = $this->initializeFrontendUser();
        if ($user !== null) {
            $formData = (array)$this->request->getArgument(SetupForm::FORM_NAME);

            $action = null;
            if ($this->request->hasArgument('enable')) {
                $user->enableOneTimePassword($formData['secret']);
                $action = 'enable';
            } elseif ($this->request->hasArgument('disable')) {
                $user->disableOneTimePassword();
                $action = 'disable';
            }

            if ($action !== null) {
                $this->frontendUserRepository->update($user);

                $event = new ToggleTotpEvent($action, $user);
                $this->eventDispatcher->dispatch($event);

                $this->addFlashMessage(
                    $this->translate('success.' . $action . '.body'),
                    $this->translate('success.title'),
                    FlashMessage::OK
                );
            }
        }

        return $this->redirect('index');
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

    protected function validateUpdateRequest(): ?ResponseInterface
    {
        if (!$this->request->hasArgument(SetupForm::FORM_NAME)) {
            return new RedirectResponse('index');
        }

        /** @var ExtbaseRequestParameters $extbaseRequestParameters */
        $extbaseRequestParameters = clone $this->request->getAttribute('extbase');
        $originalResult = $extbaseRequestParameters->getOriginalRequestMappingResults();

        $formObject = $this->getFormObject();
        $results = $this->setupFormValidator->validate($formObject);

        $results->merge($originalResult);

        if ($results->hasErrors()) {
            return (new ForwardResponse('index'))
                ->withArgumentsValidationResult($results);
        }

        return null;
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

    protected function initializeFrontendUser(): ?FrontendUser
    {
        $userId = $this->getFrontendUser()['uid'];
        return $this->frontendUserRepository->findByUid($userId);
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

    protected function translate(string $key): string
    {
        return LocalizationUtility::translate($key, 'mfa_frontend');
    }
}
