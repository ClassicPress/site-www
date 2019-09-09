=== WPScan ===
Contributors: ethicalhack3r, xfirefartx, erwanlr
Tags: wpscan, wpvulndb, security, vulnerability, hack, scan, exploit, secure, alerts
Requires at least: 3.4
Tested up to: 5.2.2
Stable tag: 1.2
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

== Screenshots ==

1. List of vulnerabilities and icon at Admin Bar.
2. Notification settings.

== Changelog ==

= 1.2 =
* Add notice about paid licenses

= 1.1 =
* Warn if API Limit was hit

= 1.0 =
* First release.
