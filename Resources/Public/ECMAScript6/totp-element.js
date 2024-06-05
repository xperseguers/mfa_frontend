/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: @causal/mfa-frontend/totp-element
 * @exports @causal/mfa-frontend/totp-element
 */
class TotpElement {
    constructor() {
        let selectors = {
                iFrame: 'body',
                inputEnable: '[data-formengine-input-name$="[tx_mfafrontend_enable]"],[name$="[tx_mfafrontend_enable]"]',
                inputSecret: '[name$="[tx_mfafrontend_secret]"]',
                inputOtp: '[name$="[tx_mfafrontend_otp]"]',
                formSection: '.form-section'
            },
            iFrame = document.querySelector(selectors.iFrame),
            inputEnable = iFrame.querySelector(selectors.inputEnable);

        inputEnable.addEventListener('click', update);

        function update() {
            const isEnabled = inputEnable.checked;
            const formSection = iFrame.querySelector(selectors.inputSecret).closest(selectors.formSection);

            // Possible TODO: reimplementation of jQuery's slideDown/slideUp
            if (isEnabled === true) {
                formSection.style.display = 'block';
            } else {
                formSection.style.display = 'none';
            }
        }

        // If we are early in the party
        document.addEventListener('DOMContentLoaded', update);
        // If late, thus "on time"
        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            update();
        }
    }
}

export default new TotpElement();
