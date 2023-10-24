.. include:: ../../Includes.rst.txt
.. _development-psr14-toggletotpevent:

ToggleTotpEvent
===============

This event when some users uses the plugin
"Two-Factor Authentication (2FA) Setup" to enable or disable use of 2FA for
their account.

Listening to this event allows you e.g., to synchronize the MFA status with some
external system or your own domain model if you happen to relate Frontend users
to some other domain model.


.. _development-psr14-toggletotpevent-register:

Registering a listener
----------------------

Open your extension's :file:`Configuration/Services.yaml` file and append:

.. code-block:: yaml

   YourVendor\YourExtension\EventListener\MfaFrontendListener:
     tags:
       - name: event.listener
         identifier: 'yourVendor/yourExtension'
         method: 'toggleTotp'
         event: Causal\MfaFrontend\Event\ToggleTotpEvent

Create :file:`Classes/EventListener/MfaFrontendListener.php` to read:

.. code-block:: php

   <?php
   declare(strict_types=1);

   namespace YourVendor\YourExtension\EventListener;

   use Causal\MfaFrontend\Event\ToggleTotpEvent;

   class MfaFrontendListener
   {
       public function toggleTotp(ToggleTotpEvent $event): void
       {
           $frontendUser = $event->getUser();
           // Do something like synchronizing the MFA status using:
           // $mfa = $frontendUser->getRawMfa()
       }
   }
