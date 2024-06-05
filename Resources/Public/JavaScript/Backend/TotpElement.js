// namespace: TYPO3/CMS/MfaFrontend/Backend/TotpElement

define(
  ['jquery'],
  function ($) {

    $(function () {
      let selectors = {
          iFrame: 'body',
          inputEnable: '[data-formengine-input-name$="[tx_mfafrontend_enable]"],[name$="[tx_mfafrontend_enable]"]',
          inputSecret: '[name$="[tx_mfafrontend_secret]"]',
          inputOtp: '[name$="[tx_mfafrontend_otp]"]',
          formSection: '.form-section'
        },
        iFrame = $(selectors.iFrame),
        inputEnable = iFrame.find(selectors.inputEnable);

      iFrame.on(
        'click',
        selectors.inputEnable,
        update
      );

      function update() {
        const isEnabled = inputEnable.prop('checked');
        const formSection = iFrame.find(selectors.inputSecret).closest(selectors.formSection);

        if (isEnabled === true) {
          formSection.slideDown();
        } else {
          formSection.slideUp();
        }
      }

      update();
    });

  }
);
