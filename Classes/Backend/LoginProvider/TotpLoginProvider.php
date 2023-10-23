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

namespace Causal\MfaFrontend\Backend\LoginProvider;

use TYPO3\CMS\Backend\Controller\LoginController;
use TYPO3\CMS\Backend\LoginProvider\UsernamePasswordLoginProvider;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * TOTP login provider for TYPO3 v10's Backend login form.
 *
 * This login provider overrides the Backend login from with a
 * custom template, which comes up with an additional field for
 * the One Time Password (OTP).
 *
 * @deprecated since TYPO3 v11
 */
class TotpLoginProvider extends UsernamePasswordLoginProvider
{
    public function render(
        StandaloneView $view,
        PageRenderer $pageRenderer,
        LoginController $loginController
    ): void
    {
        parent::render($view, $pageRenderer, $loginController);

        $view->setTemplatePathAndFilename(
            'EXT:mfa_frontend/Resources/Private/Templates/Backend/Login.html'
        );
    }
}
