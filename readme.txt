=== AdSense Protect ===
Contributors: Hasan Shofan
Tags: adsense, protect, invalid clicks, clicks, bot, security, logs
Requires at least: 5.0
Tested up to: 6.8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple WordPress plugin to log user visits and identify suspicious external click activity to protect your AdSense account.

== Description ==

The AdSense Protect plugin provides a robust solution for tracking external user clicks and identifying potentially invalid click activity. It helps website owners monitor traffic and report suspicious behavior to ad networks like Google AdSense.

Key Features:
* **Tracks External Clicks:** Records external link clicks to identify bot activity.
* **IP-based Analytics:** Provides a summary of suspicious IP addresses with repeated external clicks (3 or more in 24 hours).
* **Automatic Cleanup:** Automatically cleans up old log data to maintain database performance. The plugin keeps the **50,000 most recent logs** and deletes any logs older than **24 hours**. This ensures your database remains fast and efficient.
* **Manual Control:** Allows administrators to manually delete all logs with a single click.
* **Data Export:** Exports suspicious external clicks into a clean CSV file for easy reporting. The exported file groups identical clicks (same IP, user agent, and link) and is sorted by the number of clicks.

== Installation ==

1.  Upload the `adsense-protect` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the 'AdSense Protect' menu item in your WordPress admin dashboard to view the logs and manage settings.

== Screenshots ==

(No screenshots available yet)

== Changelog ==

= 1.0.0 =
* Initial release.

== Frequently Asked Questions ==

= How do I report invalid clicks to AdSense? =
You can use the data from the 'Suspicious IPs' table and the generated CSV file to fill out Google AdSense's [Invalid Clicks Contact Form](https://support.google.com/adsense/contact/invalid_clicks_contact).

= Is this plugin a replacement for my ad network's security? =

No. This plugin is a simple tool to help you monitor and identify suspicious activity. You should always follow your ad network's policies and use their official reporting tools.
