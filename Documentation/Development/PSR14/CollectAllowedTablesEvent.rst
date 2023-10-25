.. include:: ../../Includes.rst.txt
.. _development-psr14-collectallowedtablesevent:

CollectAllowedTablesEvent
=========================

This event is triggered when the extension collects the allowed tables to react
on. This is typically the case when it hooks into
``TYPO3\CMS\Core\DataHandling\DataHandler`` to check whether MFA (currently
TOTP) may be enabled or disabled after editing a record and trying to save it.

If you want to use MFA for your own extension, you can listen to this event and
add your own table(s) to the list of allowed tables (by default ``fe_users``).


.. _development-psr14-collectallowedtablesevent-register:

Registering a listener
----------------------

Open your extension's :file:`Configuration/Services.yaml` file and append:

.. code-block:: yaml

   YourVendor\YourExtension\EventListener\MfaFrontendListener:
     tags:
       - name: event.listener
         identifier: 'yourVendor/yourExtension'
         method: 'collectAllowedTables'
         event: Causal\MfaFrontend\Event\CollectAllowedTablesEvent

Create :file:`Classes/EventListener/MfaFrontendListener.php` to read:

.. code-block:: php

   <?php
   declare(strict_types=1);

   namespace YourVendor\YourExtension\EventListener;

   use Causal\MfaFrontend\Event\CollectAllowedTablesEvent;

   class MfaFrontendListener
   {
       public function collectAllowedTables(CollectAllowedTablesEvent $event): void
       {
           $tables = $event->getTables();
           $tables[] = 'tx_yourextension_domain_model_yourmodel';
           $event->setTables($tables);
       }
   }

.. _development-psr14-collectallowedtablesevent-tca:

Extend your TCA
---------------

You need to add a new field to your TCA to store the MFA secret. The field
**must** be named ``mfa``.

Edit :file:`ext_tables.sql` and add the field to your table:

.. code-block:: sql

   CREATE TABLE tx_yourextension_domain_model_yourmodel (
       ...
       mfa mediumblob
   );

Create
:file:`Configuration/TCA/Overrides/tx_yourextension_domain_model_yourmodel.php`
to add the ``mfa`` field as ``passthrough`` and two virtual fields for the
setup:

.. code-block:: php

   <?php
   defined('TYPO3') || die();

   $tempColumns = [
       'tx_mfafrontend_enable' => [
           'exclude' => false,
           'label' => 'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:fe_users.tx_mfafrontend_enable',
           'config' => [
               'type' => 'check',
               'renderType' => 'checkboxToggle',
               'items' => [
                   [
                       0 => '',
                       1 => '',
                   ]
               ],
           ]
       ],
       'tx_mfafrontend_secret' => [
           'exclude' => false,
           'label' => 'LLL:EXT:mfa_frontend/Resources/Private/Language/locallang_db.xlf:fe_users.tx_mfafrontend_secret',
           'config' => [
               'type' => 'user',
               'renderType' => 'MfaFrontendTotp',
           ]
       ],
       'mfa' => [
           'config' => [
               'type' => 'passthrough',
           ],
       ],
   ];

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
       'tx_yourextension_domain_model_yourmodel',
       $tempColumns
   );

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
       'tx_yourextension_domain_model_yourmodel',
       'tx_mfafrontend_enable,tx_mfafrontend_secret',
       '',
       'after:password' // Add the 2FA after our custom field "password"
   );
