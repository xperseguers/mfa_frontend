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

namespace Causal\MfaFrontend\Domain\Form;

/**
 * Setup form values object
 *
 * Use this class to hold setup form values that either originate from a
 * submitted setup form or to pass initial values of a form to the view.
 *
 * @see \Causal\MfaFrontend\Controller\SetupController
 */
class SetupForm
{
    public const FORM_NAME = 'MfaFrontendSetupForm';

    protected string $secret;

    protected string $oneTimePassword;

    protected string $checksum;

    public function __construct(string $secret, string $oneTimePassword = '', string $checksum = '')
    {
        $this->setSecret($secret);
        $this->setOneTimePassword($oneTimePassword);
        $this->setChecksum($checksum);
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    public function getOneTimePassword(): string
    {
        return $this->oneTimePassword;
    }

    public function setOneTimePassword(string $oneTimePassword): self
    {
        $this->oneTimePassword = $oneTimePassword;
        return $this;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): self
    {
        $this->checksum = $checksum;
        return $this;
    }
}
