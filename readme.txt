=== AdSense Protect ===
Contributors: Hasan Shofan
Tags: adsense, protect, invalid clicks, bot, security, logs
Requires at least: 5.0
Tested up to: 6.8.2
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link:

A robust and flexible solution to protect your AdSense account from invalid clicks and bot activity.

== Description ==

AdSense Protect is a robust WordPress plugin designed to help website owners identify and manage invalid click activity. It provides a simple, yet powerful, solution for monitoring traffic and generating reports for ad networks like Google AdSense.

The plugin tracks external clicks and analyzes user behavior to detect suspicious patterns. Its clean admin interface makes it easy to view logs, identify problematic IPs, and export data for official reporting.

= Main Features =
- **Automatic External Click Tracking:** Automatically logs clicks on external links to identify suspicious traffic.
- **Detailed IP Analytics:** Provides a summary of suspicious IP addresses that show a high number of external clicks (3 or more in 24 hours).
- **Efficient Database Management:** Automatically cleans up logs older than 24 hours and maintains a maximum of 50,000 records to ensure optimal database performance.
- **Actionable Data Export:** Exports suspicious external click data into a formatted CSV file, with identical entries grouped and sorted by click count.
- **Manual Control:** Offers an option to manually delete all log data instantly.

= Usage =

= Viewing Logs =
After activating the plugin, navigate to the **"AdSense Protect"** menu item in your WordPress dashboard. Here, you can view a paginated list of all recorded clicks, see a summary of suspicious IPs, and access the export and delete options.

= Generating a Report =
To generate a report for AdSense, simply click the **"Export External Clicks"** button on the plugin's admin page. The generated CSV file will contain all the necessary data, grouped by user and sorted by click count, making it easy to fill out Google's Invalid Clicks Contact Form.

= Customizing Behavior =
For advanced users, the plugin's main file includes a setting to enable or disable internal visit logging, ensuring the plugin only records the data you need.

== Changelog ==

= 1.0.0 =
* Initial release.

== Frequently Asked Questions ==

= How do I report invalid clicks to AdSense? =
Use the data provided by this plugin to fill out the official [Google AdSense Invalid Clicks Contact Form](https://support.google.com/adsense/contact/invalid_clicks_contact).

= Is this plugin a replacement for my ad network's security? =
No. This plugin is a simple tool to help you monitor and identify suspicious activity. You should always follow your ad network's policies and use their official reporting tools.

