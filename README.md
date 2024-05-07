This module is a plugin for the OAuth2 Client module, and also requires the PHPMailer SMTP 
module. It provides authentication to Google Workspace Gmail via OAuth2.  

IMPORTANT:  This module requires the Google league/oauth2-google module, available at 
https://oauth2-client.thephpleague.com/providers/league/.  It should be installed in
your vendor directory.

Before using this module, it is necessary to set up a Client ID and Client Secret
on Google.  Here is how to do that as of April, 2024, and configure this module:

1.  Sign in to your Google Workspace account.

2.  Go to https://console.cloud.google.com/.

3.  At the top left of the page next to "Google Cloud", there should be a projects dropdown. 
    Click on it.

4.  If you have no projects listed in the 'Select a resource' dialog box, create one,
    then select it as your current project.

5.  Click on 'APIs & Services', a large button somewhere in the center of the page.

6.  Click on Credentials in the menu that appears on the left-hand side of the page.

7.  Click on CREATE CREDENTIALS, then select 'OAuth client ID'.

8.  Select 'Web application' for Application Type.

9.  Name your credentials, and note your Client ID and Client Secret.  You will need to 
    enter these values in (11) below.

10. The 'Authorized redirect URI' for your site will be:

```
	https://yoursite.com/oauth2-client/phpmailer_plugin/code
```
    Replace "yoursite.com" with your site domain.

11. In Drupal, go to Configuration->System->OAuth2 Clients (/admin/config/system/oauth2-client).
    You should see this plugin (PHPMailer Gmail Oauth2).  Enable it, then press Edit.  Enter your
    Client ID, and Client Secret and then press 'Save'. 

12. After you've saved your Google credentials, press the 'Save and request token' button
    to redirect from your site to Google, then follow the instructions on Google.  
    When you're done, Google should issue a Refresh Token and redirect back to the Drupal page you 
    just left.  If everything worked, you will have your Refresh Token and are ready to send email.

A few other setup items:

1.  Don't forget to go to the Mail module to select 'PHPMailer SMTP' as your mail Formatter and Sender
    (/admin/config/system/mailsystem).

2.  If you have other modules such as Commerce 2 that send mail, you may need to add them at the 
    bottom of the same page in (1) above.

3.  On the PHPMailer SMTP transport settings page, select 'Gmail OAuth2' as your SMTP authentication method
    (/admin/config/system/phpmailer-smtp).  Set the SMTP port to 587, and select TLS as the secure protocol.

You can use the 'Test configuration' service on the PHPMailer SMTP transport settings page to verify that
the new configuration is working by sending a test email.
