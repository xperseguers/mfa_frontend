.. include:: ../Includes.rst.txt
.. _usage-backend:

Management of MFA in the Backend
================================

When you edit a Frontend User (``fe_users``), you will see a new tab called
"Multi-Factor Authentication". There, you can enable MFA for the user by
toggling the switch and following the instructions:

.. image:: ../Introduction/Images/mfa-tca.png
   :alt: Configuration of the MFA settings for a Frontend user

Once you have enabled MFA for a user, you will have to enter a valid TOTP code
together with toggling off the switch to disable MFA again.
