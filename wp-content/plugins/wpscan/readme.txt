=== WPScan ===
Contributors: ethicalhack3r, xfirefartx, erwanlr
Tags: wpscan, wpvulndb, security, vulnerability, hack, scan, exploit, secure, alerts
Requires at least: 3.4
Tested up to: 5.2.4
Stable tag: 1.4
Requires PHP: 5.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.html

Scans your system for vulnerabilities listed in the WPScan Vulnerability Database.

== Description ==

This plugin scans your system on a daily basis to find vulnerabilities listed in the [WPScan Vulnerability Database](https://wpvulndb.com/). It shows an icon on the Admin Toolbar with the total number of vulnerabilities found.

= What does the plugin do? =

* Scans the WordPress core, plugins and themes for known vulnerabilities;
* Shows an icon on the Admin Toolbar with the total number of vulnerabilities found;
* Notifies you by mail when new vulnerabilities are found.

= Further Reading =

* The [WPScan](https://wpscan.org/) official homepage.
* The [WPScan Vulnerability Database](https://wpvulndb.com/).
* The official [Twitter](https://twitter.com/_wpscan_) account.

== Installation ==

1. Upload `wpscan.zip` content to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. [Register](https://wpvulndb.com/users/sign_up) for a free API token
4. Save the API token to the WPScan settings page

== FAQ ==

* How many API calls are made?
  There is one API call for the WordPress version, one call for each installed plugin and one for each theme, daily.

* Why is the "Summary" section and the "Check Now" button not showing?
  The cron job did not run, which can be due to:
    - The DISABLE_WP_CRON constant is set to true in the wp-config.php file, but no system cron has been set (crontab -e).
    - A plugin's caching pages is enabled (see https://wordpress.stackexchange.com/questions/93570/wp-cron-doesnt-execute-when-time-elapses?answertab=active#tab-top).
    - The blog is unable to make a loopback request, see the Tools->Site Health for details.
  If the issue can not be solved with the above, putting define('ALTERNATE_WP_CRON', true); in the wp-config.php
  could help, however, will reduce the SEO of the blog.

== Screenshots ==

1. List of vulnerabilities and icon at Admin Bar.
2. Notification settings.

== Changelog ==

= 1.4 =
* Prevent multiple tasks to run simultaneously
* Check Now Button disabled and Spinner icon displayed when a task is already running
* Results page automatically reloaded when Task is finished (checked every 10s)

= 1.3 =
* Use the /status API endpoint to determine if the Token is valid. As a result, a call is no longer consumed when setting/changing the API token.
* Trim and remove potential leading 'v' in versions when comparing then with the fixed_in values.

= 1.2 =
* Add notice about paid licenses

= 1.1 =
* Warn if API Limit was hit

= 1.0 =
* First release.
