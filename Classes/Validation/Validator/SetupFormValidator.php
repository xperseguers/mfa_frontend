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

namespace Causal\MfaFrontend\Validation\Validator;

use Causal\MfaFrontend\Validation\Validator\Traits\SetupFormValidatorPhp8Trait;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractGenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

if ((new Typo3Version())->getMajorVersion() >= 12) {
    class SetupFormValidator extends AbstractGenericObjectValidator {
        use SetupFormValidatorPhp8Trait;
    }
} else {
    class SetupFormValidator extends GenericObjectValidator {
        use SetupFormValidatorPhp8Trait;
    }
}
