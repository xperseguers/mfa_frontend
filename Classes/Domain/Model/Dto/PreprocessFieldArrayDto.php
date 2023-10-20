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

namespace Causal\MfaFrontend\Domain\Model\Dto;

use TYPO3\CMS\Core\DataHandling\DataHandler;

class PreprocessFieldArrayDto
{
    protected array $fieldArray;

    protected string $table;

    protected int $id;

    protected DataHandler $dataHandler;

    public function __construct(
        array &$fieldArray,
        string $table,
        int $id,
        DataHandler $dataHandler
    )
    {
        $this->fieldArray = &$fieldArray;
        $this->table = $table;
        $this->id = $id;
        $this->dataHandler = $dataHandler;
    }

    public function getFieldArray(): array
    {
        return $this->fieldArray;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
}
