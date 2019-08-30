=== WP SMTP ===
Contributors: yehudah
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=yehuda@myinbox.in&item_name=Donation+for+WPSMTP
Tags: wp smtp,smtp,mail,email,phpmailer,mailer,wp mail,gmail,yahoo,mail smtp,ssl,tls
License: GPLv2
Requires at least: 2.7
Tested up to: 5.2
Stable tag: 1.1.10

WP SMTP can help us to send emails via SMTP instead of the PHP mail() function. 

== Description ==

WP SMTP can help us to send emails via SMTP instead of the PHP mail() function.
It adds a settings page to "Dashboard"->"Settings"->"WP SMTP" where you can configure the email settings.
There are some examples on the settings page, you can click the corresponding icon to view (such as "Gmail""Yahoo!""Microsoft""163""QQ").
If the field "From" was not a valid email address, or the field "SMTP Host" was left blank, it will not reconfigure the wp_mail() function.

= Do you want more advanced SMTP mailer? =

* Built-in **importer for WP SMTP settings**.
* Universal SMTP for every service.
* SMTP ports are blocked? API support - A method for sending emails via HTTP for Gmail, Sendgrid, Mailgun, and Mandrill.
* Credentials can be configured inside wp-config.php insted of the DB.
* Built-in mail logger with the option to resend and filter.
* Built-in alert function when emails are faling, you can get notified by Email, Slack or pushover.
* Ports checker for any blocking issue.

**Check Post SMTP:**
[https://wordpress.org/plugins/post-smtp/](https://wordpress.org/plugins/post-smtp/)

= CREDITS =

WP SMTP plugin was originally created by BoLiQuan. It is now owned and maintained by Yehuda Hassine.

= Usage =

1. Download and extract `wp-smtp.zip` to `wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. "Dashboard"->"Settings"->"WP SMTP"
4. There are some examples on the settings page, you can click the corresponding icon to view.(such as "Gmail""Yahoo!""Microsoft""163""QQ") 
5. For more information of this plugin, please visit: [Plugin Homepage](https://wpsmtpmail.com/ "WP SMTP").

== Installation ==

1. Download and extract `wp-smtp.zip` to `wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. "Dashboard"->"Settings"->"WP SMTP"
4. There are some examples on the settings page, you can click the corresponding icon to view.(such as "Gmail""Yahoo!""Microsoft""163""QQ") 
5. For more information of this plugin, please visit: [Plugin Homepage](https://wpsmtpmail.com/ "WP SMTP").

== Changelog ==

= 1.1.10 =

New maintainer - yehudah
https://wpsmtpmail.com/v1-1-10-wp-smtp-is-back/

* Code structure and organize.
* Credentials can now be configured inside wp-config.php

= 1.1.9 =

* Some optimization

= 1.1.7 =

* Using a nonce to increase security.

= 1.1.6 =

* Add Yahoo! example
* Some optimization

= 1.1.5 =

* Some optimization

= 1.1.4 =

* If the field "From" was not a valid email address, or the field "Host" was left blank, it will not reconfigure the wp_mail() function.
* Add some reminders.

= 1.1.3 =

* If "SMTP Authentication" was set to no, the values "Username""Password" are ignored.

= 1.1.2 =

* First release.


== Screenshots ==

1. "Gmail.com" settings
2. "Yahoo.com" settings
3. "Live.com" settings
4. "163.com" settings
5. "QQ.com" settings


== Frequently Asked Questions ==

You can sumbit it in https://wordpress.org/support/plugin/wp-smtp, if It's urgent like a bug submit it here: https://wpsmtpmail.com/contact/


== Upgrade Notice ==

Please visit http://boliquan.com/wp-smtp/
