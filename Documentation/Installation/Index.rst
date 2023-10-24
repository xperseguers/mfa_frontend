.. include:: ../Includes.rst.txt
.. _installation:

Installation
============

This extension may be installed like any other extension, either from the TYPO3
Extension Repository (TER) or from Packagist:

.. code-block:: bash

   composer require causal/mfa-frontend


.. _migration:

Migration from EXT:cf_google_authenticator
------------------------------------------

This extension comes with a migration wizard to migrate existing Google
Authenticator configuration from both Backend and Frontend users to the new data
structure.

Since TYPO3 v11, MFA is natively supported for Backend users, thus the
migration wizard will migrate configuration to the native configuration format
for TYPO3.

Unfortunately TYPO3 does not correctly support MFA for Frontend users yet
(there is a bug described on `Forge <https://forge.typo3.org/issues/102081>`__),
so the migration from former extension EXT:cf_google_authenticator is done in a
custom ``mfa_frontend`` field for Frontend users.

**Steps:**

.. warning::

   If TYPO3 suggests it, be sure NOT to rename or drop legacy columns
   ``tx_cfgoogleauthenticator_enabled`` and ``tx_cfgoogleauthenticator_secret``
   for tables ``be_users`` and ``fe_users`` (yet).

1. Switch to the module "Upgrade" within "Admin Tools"
2. Click the button "Upgrade Wizard"
3. Run upgrade wizard "Migrate TOTP settings from cf_google_authenticator"

.. hint::

   At this point, you may safely rename/drop legacy columns as described above.


.. _migration-custom:

Migration from your own domain model tables
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you have implemented Two-factor authentication (2FA) using
EXT:cf_google_authenticator's signals to reuse its business logic, you may do so
with this extension as well (using :ref:`PSR-14 events <>` naturally).

1. Listen to the PSR-14 event :ref:`development-psr14-collectallowedtablesevent`
   and add your own table to the list of allowed tables for MFA.
2. Adapt your TCA to use a new field ``mfa`` that shall replace the two former
   fields ``tx_cfgoogleauthenticator_enabled`` and
   ``tx_cfgoogleauthenticator_secret`` in your domain model.

   Please see :ref:`development-psr14-collectallowedtablesevent-tca` for full
   instructions.

.. warning::

   You **must** name the MFA field `mfa` in your domain model; and not
   `mfa_frontend` as we do for Frontend users records.

Then just use the migration wizard as described above.
