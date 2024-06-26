# MFA Frontend

[![Latest Stable Version](https://poser.pugx.org/causal/mfa-frontend/v/stable)](https://extensions.typo3.org/extension/mfa_frontend/)
![GitHub license](https://img.shields.io/github/license/xperseguers/mfa_frontend.svg?style=flat-square&label=License)
[![Crowdin](https://badges.crowdin.net/typo3-extension-mfafrontend/localized.svg)](https://crowdin.com/project/typo3-extension-mfafrontend)
[![Total Downloads](https://poser.pugx.org/causal/mfa-frontend/d/total)](https://packagist.org/packages/causal/mfa-frontend)

This extension adds support for Multi-Factor Authentication (MFA) to TYPO3's
Frontend.

It has been initially inspired by the extension
[[codeFareith] Google Authenticator](https://extensions.typo3.org/extension/cf_google_authenticator),
but it has been rewritten and extended as that former extension was not
actively maintained anymore. Thanks to the original author
Robin "codeFareith" von den Bergen for the inspiration!

## Current features

- Support for Google Authenticator or similar applications (TOTP).
- Plugin to add/remove TOTP setup to one's profile (`fe_users`).
- Support for removing MFA protection for a given user ("administrator mode")
  as any larger TYPO3 installation will have for sure a few users who will
  lose/change their phone or access to their MFA application and will need to
  be helped by an administrator.

## Planned features

- Support for backup codes.
- Support for other MFA providers (e.g., Yubikey).


## License

[GNU Public License](https://opensource.org/license/gpl-3-0/)
