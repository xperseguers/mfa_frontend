.. include:: ../Includes.rst.txt
.. _usage-applications:

Applications
============

In order to use the MFA feature, you need to install some Authenticator
application. There are many of them, and they are available for all platforms.

Since this extension currently supports
`Time-based one-time password <https://en.wikipedia.org/wiki/Time-based_One-time_Password_algorithm>`__,
you need to install an application that supports this algorithm.

This application may be a browser extension, a desktop application, or an
app on your mobile device (smartphone/tablet).

If you look for an Authenticator application for your mobile device, we can name
a few of them:

- Google Authenticator
  (`iOS / iPadOS <https://apps.apple.com/ch/app/google-authenticator/id388497605>`__,
  `Android <https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2>`__)
- Microsoft Authenticator
  (`iOS / iPadOS <https://apps.apple.com/ch/app/microsoft-authenticator/id983156458>`__,
  `Android <https://play.google.com/store/apps/details?id=com.azure.authenticator>`__)
- Twilio Authy
  (`iOS / iPadOS <https://apps.apple.com/ch/app/twilio-authy/id494168017>`__,
  `Android <https://play.google.com/store/apps/details?id=com.authy.authy>`__)

If however you already use a password manager, you may want to use it to store
your MFA secrets. There are many password managers, but two well-known of them
officially support storing MFA secrets together with your website's credentials:

- `1Password <https://1password.com/>`__
- `Bitwarden <https://bitwarden.com/>`__

If you use 1Password, you can use the following link to see how to configure
OTP for an existing Login entry (username + password) you have in your vault:
https://support.1password.com/one-time-passwords/.
