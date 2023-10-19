# MFA Frontend

This extension adds support for MFA (Multi-Factor Authentication) to TYPO3's Frontend.

It has been initially inspired by the extension
[[codeFareith] Google Authenticator](https://extensions.typo3.org/extension/cf_google_authenticator)
but it has been rewritten and extended to ultimately support other MFA providers.
Thanks to the original author Robin "codeFareith" von den Bergen for the inspiration!

## Current features

- Support for Google Authenticator or similar applications (TOTP).
- Plugin to add/remove TOTP setup to one's profile (`fe_users`).

## Planned features

- Support for removing MFA protection for a given user ("administrator mode") as
  any larger TYPO3 installation will have for sure a few users who will loose/change
  their phone or access to their MFA application and will need to be helped by an
  administrator.
- Support for other MFA providers (e.g., Yubikey).
