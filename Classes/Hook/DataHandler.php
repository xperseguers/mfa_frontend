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

namespace Causal\MfaFrontend\Hook;

use Causal\MfaFrontend\Domain\Model\Dto\PreprocessFieldArrayDto;
use Causal\MfaFrontend\Event\CollectAllowedTablesEvent;
use Causal\MfaFrontend\Handler\TotpSetupHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandler
{
    protected EventDispatcherInterface $eventDispatcher;

    protected ?TotpSetupHandler $totpSetupHandler = null;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Hooks into \TYPO3\CMS\Core\DataHandling\DataHandler to handle the
     * activation/deactivation of the MFA for a fe_users record.
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param int|string $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        string $table,
        $id,
        \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
    ): void
    {
        $event = new CollectAllowedTablesEvent([
            'fe_users',
        ]);
        $this->eventDispatcher->dispatch($event);

        if (!in_array($table, $event->getTables(), true)) {
            return;
        }

        $otpFromFieldArray = &$incomingFieldArray['tx_mfafrontend_otp'];
        // TODO: Is next line really needed?
        $otpFromPostData = $_POST['data'][$table]['tx_mfafrontend_otp'] ?? null;

        if ($otpFromFieldArray === null && $otpFromPostData !== null) {
            $otpFromFieldArray = $otpFromPostData;
        }

        $secretFromFieldArray = &$incomingFieldArray['tx_mfafrontend_secret'];
        // TODO: Is next line really needed?
        $secretFromPostData = $_POST['data'][$table]['tx_mfafrontend_secret'] ?? null;

        if ($secretFromFieldArray === null && $secretFromPostData !== null) {
            $secretFromFieldArray = $secretFromPostData;
        }

        $preprocessFieldArrayDto = $this->getPreprocessFieldArrayDto(
            $incomingFieldArray,
            $table,
            is_numeric($id) ? $id : 0,
            $pObj
        );
        $result = $this->getTotpSetupHandler()->process($preprocessFieldArrayDto);

        $incomingFieldArray = array_merge($incomingFieldArray, $result);
    }

    /**
     * @param array $fieldArray
     * @param string $table
     * @param int $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return PreprocessFieldArrayDto
     */
    protected function getPreprocessFieldArrayDto(
        array &$fieldArray,
        string $table,
        int $id,
        \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
    ): PreprocessFieldArrayDto
    {
        return GeneralUtility::makeInstance(
            PreprocessFieldArrayDto::class,
            $fieldArray,
            $table,
            $id,
            $dataHandler
        );
    }

    /**
     * @return TotpSetupHandler
     */
    protected function getTotpSetupHandler(): TotpSetupHandler
    {
        if ($this->totpSetupHandler === null) {
            $this->totpSetupHandler = GeneralUtility::makeInstance(TotpSetupHandler::class);
        }

        return $this->totpSetupHandler;
    }
}
