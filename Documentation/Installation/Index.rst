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
   for tables ``be_users`` and ``fe_users`` (yet)

1. Switch to the module "Upgrade" within "Admin Tools"
2. Click the button "Upgrade Wizard"
3. Run upgrade wizard "Migrate TOTP settings from cf_google_authenticator"

.. hint::

   At this point, you may safely rename/drop legacy columns as described above.


.. _migration-custom:

Migration from your own domain model tables
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you have implemented 2FA using EXT:cf_google_authenticator's signals
to reuse its business logic, you may do so with this extension as well (using
PSR-14 events naturally).

You will need to adapt your TCA to use a new field ``mfa`` that shall replace
the two former fields ``tx_cfgoogleauthenticator_enabled`` and
``tx_cfgoogleauthenticator_secret`` in your domain model.

Please see :file:`Configuration/TCA/Overrides/fe_users.php` for the actual
configuration to be reused in your domain model.

.. warning::

   You **must** name your custom MFA field `mfa` and not `mfa_frontend` as we do
   for Frontend users records.

Then just use the migration wizard as described above.
