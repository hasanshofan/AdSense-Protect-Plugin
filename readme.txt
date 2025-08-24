=== Invalid Traffic Logger ===
Contributors: HasanShofan
Tags: Analysis, protect, invalid clicks, bot, traffic
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://paypal.me/WikiArabiaAT?country.x=AT&locale.x=en_US

A robust and flexible solution for analyzing user behavior and external clicks on products, empowering you to study landing page performance and boost your revenue.

== Description ==

"Invalid Traffic Logger" is a WordPress plugin that records and aggregates visit data on your website, such as clicks from the same IP address on external links. This tool helps you monitor visitor activity and identify user behavior when clicking on external links. You can also monitor the performance of a product or landing page without the need for complex programming, allowing you to take the necessary actions to improve the user experience or change content, which in turn improves revenue from products or external clicks.

With this plugin, you can study user behavior and how they navigate the site or which links they were interested in. You can also detect unusual behavior, such as a single user excessively clicking on certain links.

Note: This plugin does not track clicks on links within an iframe component, such as those used by Google AdSense. Therefore, you cannot rely on it to analyze visitor clicks on this type of advertisement.

The plugin tracks external clicks and analyzes user behavior to detect suspicious patterns. Its clean admin interface makes it easy to view logs, identify problematic IPs, and export data for official reporting.

=== Main Features ===
- **Automatic External Click Tracking:** Automatically records clicks on all external links to monitor user navigation and identify unusual activity.
- **Detailed User Behavior Analytics:** Provides a summary of suspicious IP addresses that exhibit unusual clicking behavior (3 or more clicks on external links within a 24-hour period).
- **Efficient Database Management:** Automatically cleans up logs older than 24 hours and maintains a maximum of 50,000 records to ensure optimal database performance.
- **Actionable Data Export:** Exports suspicious external click data into a formatted CSV file, with identical entries grouped and sorted by click count.
- **Manual Control:** Offers an option to manually delete all log data instantly to keep your database clean.

== Contributing ==

Developers can contribute to the source code on the [Github Repository](https://github.com/hasanshofan/AdSense-Protect-Plugin).

== Usage ==
= Viewing Logs =
After activating the plugin, navigate to the "Invalid Traffic Logger" menu item in your WordPress dashboard. Here, you can view a paginated list of all recorded clicks, see a summary of suspicious IPs, and access the export and delete options.

= Generating a Report =
To generate a report, simply click the "Export External Clicks" button on the plugin's admin page. The generated CSV file will contain all the necessary data, grouped by user and sorted by click count, making it easy to analyze external link activity on your site.

= Customizing Behavior =
For advanced users, the plugin's main file includes a setting to enable or disable internal visit logging, ensuring the plugin only records the data you need.

== Changelog ==
= 1.0.0 =

*Initial release.

== Frequently Asked Questions ==
= How do I analyze suspicious external clicks? =
Use the data provided by this plugin to identify and analyze unusual clicking behavior on your external links. You can then use this information to improve your content or user experience.

= Is this plugin a replacement for my ad network's security? =
No. This plugin is a simple tool to help you monitor and identify suspicious activity on your external links. You should always follow your ad network's policies and use their official reporting tools for any ad-related concerns.

= Can this plugin track clicks on Google AdSense ads? =
No. This plugin cannot track clicks on links located inside iframe components due to browser security reasons. Google AdSense ads appear inside iframes, and therefore, the plugin cannot track them.
