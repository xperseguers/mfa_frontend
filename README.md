# MFA Frontend

This extension adds support for MFA (Multi-Factor Authentication) to TYPO3's
Frontend.

It has been initially inspired by the extension
[[codeFareith] Google Authenticator](https://extensions.typo3.org/extension/cf_google_authenticator)
but it has been rewritten and extended as that former extension was not
actively maintained anymore. Thanks to the original author
Robin "codeFareith" von den Bergen for the inspiration!

## Current features

- Support for Google Authenticator or similar applications (TOTP).
- Plugin to add/remove TOTP setup to one's profile (`fe_users`).

## Planned features

- Support for removing MFA protection for a given user ("administrator mode")
  as any larger TYPO3 installation will have for sure a few users who will
  loose/change their phone or access to their MFA application and will need to
  be helped by an administrator.
- Support for backup codes.
- Support for other MFA providers (e.g., Yubikey).

## Migration from EXT:cf_google_authenticator

This extension comes with a migration wizard to migrate existing
Google Authenticator configuration from both Backend and Frontend users to the
new data structure.

Since TYPO3 v11, MFA is natively supported for Backend users, thus the
migration wizard will migrate configuration to the native configuration format
for TYPO3.

Unfortunately TYPO3 does not correctly support MFA for Frontend users yet
(there is a bug described on [Forge](https://forge.typo3.org/issues/102081), so
the migration from former extension EXT:cf_google_authenticator is done in a
custom `mfa_frontend` field for Frontend users.

Steps:

1. **If TYPO3 suggests it, be sure NOT to rename or drop legacy columns
   `tx_cfgoogleauthenticator_enabled` and `tx_cfgoogleauthenticator_secret`** for
   tables `be_users` and `fe_users` (yet)
2. Switch to the module "Upgrade" within "Admin Tools"
3. Click the button "Upgrade Wizard"
4. Run upgrade wizard "Migrate TOTP settings from cf_google_authenticator"

At this point, you may safely rename/drop legacy columns as described above.

## Migration from your own domain model tables

If you have implemented 2FA using EXT:cf_google_authenticator's signals
to reuse its business logic, you may do so with this extension as well. You
will need to adapt your TCA to use a new field `mfa` (**BEWARE:** not
`mfa_frontend` if you plan to benefit from our migration wizard) that whall
replace fields `tx_cfgoogleauthenticator_enabled` and
`tx_cfgoogleauthenticator_secret` in your domain model.

Please see `Configuration/TCA/Overrides/fe_users.php` for details (but, again,
name your custom MFA field `mfa` and not `mfa_frontend`).

Then just use the migration wizard as described above.
