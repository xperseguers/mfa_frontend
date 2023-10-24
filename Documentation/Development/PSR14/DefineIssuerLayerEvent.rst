.. include:: ../../Includes.rst.txt
.. _development-psr14-defineissuerlayerevent:

DefineIssuerLayerEvent
======================

This event lets you configure/override the issuer layer is defined. You could
use it to show your extension's name when you use MFA for your own domain model.


.. _development-psr14-defineissuerlayerevent-register:

Registering a listener
----------------------

Open your extension's :file:`Configuration/Services.yaml` file and append:

.. code-block:: yaml

   YourVendor\YourExtension\EventListener\MfaFrontendListener:
     tags:
       - name: event.listener
         identifier: 'yourVendor/yourExtension'
         method: 'defineIssuerLayer'
         event: Causal\MfaFrontend\Event\DefineIssuerLayerEvent

Create :file:`Classes/EventListener/MfaFrontendListener.php` to read:

.. code-block:: php

   <?php
   declare(strict_types=1);

   namespace YourVendor\YourExtension\EventListener;

   use Causal\MfaFrontend\Event\DefineIssuerLayerEvent;

   class MfaFrontendListener
   {
       public function defineIssuerLayer(DefineIssuerLayerEvent $event): void
       {
           if ($event->getTable() === 'tx_yourextension_domain_model_yourmodel') {
               $event->setLayer('Your Custom Name');
           }
       }
   }
