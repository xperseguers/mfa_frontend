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

namespace Causal\MfaFrontend\Handler;

use Causal\MfaFrontend\Domain\Model\Dto\PreprocessFieldArrayDto;
use Causal\MfaFrontend\Domain\Model\Dto\TotpSettingsDto;
use Causal\MfaFrontend\Domain\Model\TotpSettings;
use Causal\MfaFrontend\Traits\VerifyOtpTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TotpSetupHandler
{
    use VerifyOtpTrait;

    protected EventDispatcherInterface $eventDispatcher;

    protected ?PreprocessFieldArrayDto $preprocessFieldArrayDto;

    protected ?TotpSettingsDto $totpSettingsDto;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param PreprocessFieldArrayDto $preprocessFieldArrayDto
     * @return array
     */
    public function process(PreprocessFieldArrayDto $preprocessFieldArrayDto): array
    {
        $this->preprocessFieldArrayDto = $preprocessFieldArrayDto;

        $this->initTotpSettingsDto();
        $this->checkFieldArray();

        $result = $this->totpSettingsDto
            ->getNewSettings()
            ->toArray($preprocessFieldArrayDto->getTable());

        return $result;
    }

    private function initTotpSettingsDto(): void
    {
        $this->totpSettingsDto = GeneralUtility::makeInstance(TotpSettingsDto::class);

        $fieldArray = $this->preprocessFieldArrayDto->getFieldArray();
        $newSettings = TotpSettings::createFromVirtualData($fieldArray);

        if ($this->isExistingUser()) {
            $record = $this->preprocessFieldArrayDto->getDataHandler()->recordInfo(
                $this->preprocessFieldArrayDto->getTable(),
                $this->preprocessFieldArrayDto->getId(),
                '*'
            );
            if ($record !== null) {
                $this->totpSettingsDto->setOldSettings(
                    TotpSettings::createFromRecord($record, $this->preprocessFieldArrayDto->getTable())
                );
                $newSettings->setMfa(
                    $this->totpSettingsDto->getOldSettings()->getMfa()
                );
            }
        }

        $this->totpSettingsDto->setNewSettings($newSettings);

        $this->totpSettingsDto->setOneTimePassword(
            str_replace(' ', '', $fieldArray['tx_mfafrontend_otp'])
        );
    }

    private function checkFieldArray(): void
    {
        if ($this->hasUserEnabledAuthenticator()) {
            $this->processEnableRequest();
        } elseif ($this->isExistingUser()) {
            if ($this->hasUserDisabledAuthenticator()) {
                $this->processDisableRequest();
            } else {
                $this->keepOldSettings();
            }
        }
    }

    private function isExistingUser(): bool
    {
        return $this->preprocessFieldArrayDto->getId() !== 0;
    }

    private function hasUserEnabledAuthenticator(): bool
    {
        $hasEnabled = $this->totpSettingsDto->getNewSettings()->isEnabled();

        if ($this->isExistingUser()) {
            $hasEnabled &= ($this->totpSettingsDto->getOldSettings()->isEnabled() === false);
        }

        return (bool)$hasEnabled;
    }

    private function hasUserDisabledAuthenticator(): bool
    {
        return $this->totpSettingsDto->getNewSettings()->isEnabled() === false
            && $this->totpSettingsDto->getOldSettings()->isEnabled() === true;
    }

    private function processEnableRequest(): void
    {
        $isValid = $this->verifyOneTimePassword(
            $this->totpSettingsDto->getNewSettings()->getSecret(),
            $this->totpSettingsDto->getOneTimePassword()
        );

        if ($isValid) {
            $this->enableAuthenticator();
        } elseif ($this->isExistingUser()) {
            $this->keepOldSettings();
        }
    }

    private function processDisableRequest(): void
    {
        $isValid = $this->verifyOneTimePassword(
            $this->totpSettingsDto->getOldSettings()->getSecret(),
            $this->totpSettingsDto->getOneTimePassword()
        );

        if ($isValid) {
            $this->disableAuthenticator();
        } else {
            $this->keepOldSettings();
        }
    }

    private function enableAuthenticator(): void
    {
        $newSettings = $this->totpSettingsDto->getNewSettings();
        $newSettings->setEnabled(true);
    }

    private function disableAuthenticator(): void
    {
        $newSettings = $this->totpSettingsDto->getNewSettings();
        $newSettings->setEnabled(false);
        $newSettings->setSecret('');
    }

    private function keepOldSettings(): void
    {
        $oldSettings = $this->totpSettingsDto->getOldSettings();
        $newSettings = $this->totpSettingsDto->getNewSettings();

        $newSettings->setEnabled($oldSettings->isEnabled());
        $newSettings->setSecret($oldSettings->getSecret());
    }
}
