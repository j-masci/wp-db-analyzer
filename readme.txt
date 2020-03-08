=== Database Analyzer ===
Contributors: joelmasci
Tags: database, reports, metrics, developer
Requires at least: 4.0
Tested up to: 5.3
Stable tag: 1.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reports on the size and structure of your database. Shows where your data is distributed among your database tables.

== Description ==

Reports on the size and structure of your database. Shows where your data is distributed among your database tables.

**Note: This plugin is somewhat geared towards developers.**

See the FAQ for more info.

Some **screenshots** can be found here for the time being: https://github.com/j-masci/wp-db-analyzer

== Frequently Asked Questions ==

= What does the plugin do? =
	It generates a number of reports that display information about your database.

= What is in each report? =
    Reports contain one or more tables that display data about your database. Normally, each table cell shows the number of items in your database associated with the row and column heading.

= Is the plugin meant for non-developers? =
    The plugin will be more useful for developers. The reports will make more sense if you have a basic understanding of the WordPress database structure.

= Does the plugin clean up un-used data or make my site faster? =
    No. It only reads data from your database to generate reports.

= How do I use the plugin? =
    Once activated, look for "Database Analyzer" in the sidebar. Click on it, and then select a single report or look for the button to run all reports.

= Why did you make this plugin? =
    I once had a site with a misbehaving plugin that was inserting too many items into the posts and post meta table. It had caused the post meta table to grow to about 1 million rows, which made the site slow in many cases. It was easy to find out how large the post meta table was but it took some investigation to figure out why. During this process I realized it would be useful to generate all of that data again for other WordPress installs. So, I made the plugin for reporting on the posts and post meta table, and then decided to add a few more reports later on for things related to options, users, terms, and comments.

== Screenshots ==

== Contributors & Developers ==

Feel free to suggest or add anything that you might find useful:

https://github.com/j-masci/wp-db-analyzer

== Changelog ==

= 1.0 =
* [Added] Initial release.