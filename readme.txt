=== WP Scheduled Themes ===
Contributors: wpgwiggum
Tags: plugin, theme, holidays, scheduled, schedule, cron, cronjob, theme switcher, holiday
Donate Link:  http://www.itegritysolutions.ca/community/wordpress/scheduled-themes
Requires at least: 3.0
Tested up to: 4.2
Version: 1.7
Stable tag: 1.7

Schedule a theme to display on the live site for holidays or special events. (Available from Appearance - Scheduled Themes)

== Description ==

This plugin allows a wordpress administrator to schedule a different theme to display on the website for holidays or special events for all visitors.

The theme will be overridden on the live site for all people who visit the site. Great for setting a Christmas theme!

If you just want to have a css file added instead of a whole theme, check out the wp-scheduled-styles plugin.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload folder `wp-scheduled-themes` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Set the themes that you want to have scheduled
1. Test and enjoy!

== Frequently Asked Questions ==

= My Theme is scheduled to appear, why does it not show up? =

This can happen if you have 2 themes scheduled to appear at the same time. The theme that was scheduled to appear the earliest in the year will always appear.

= I want to schedule changes, but I don't want to duplicate my entire theme for a minor change =

Maybe wp-scheduled-styles is the plugin for you! This plugin will let you specify an additional .css file to be included from your theme.

== Screenshots ==

1. This is the settings page. Pretty simple.

== Changelog ==

= 1.7 =
* Updated to work with latest version of WordPress
* Changed WordPress calls for getting list of themes to be more modern approach, addressing some issues being caused in rare scenarios
* [Updated April 25, 2015]

= 1.6 =
* Updated to work with latest version of WordPress
* Removed support for PHP4, which was causing some issues with logging in PHP 5.4
* [Updated December 10, 2014]

= 1.5 =
* Updated to only load JS and CSS on pertinent WP Admin screens
* [Updated April 25, 2014]

= 1.4.1 =
* Added support for multiple languages
* Added Spanish and Serbian translations (thanks to Ognjen Djuraskovic at firstsiteguide.com
* [Updated March 23, 2014]

= 1.3.1 =
* Updated to support WordPress 3.8 and new look
* Fixed Calendar (datepicker) from not appearing
* [Updated January 20, 2014]

= 1.2.1 =
* Updates supported WP version numbers
* [Updated November 22, 2012]

= 1.2 =
* Fixed compatability with WP-Scheduled-Styles (yet to be released)
* [Updated August 13, 2011]

= 1.1 =
* Fixed error where installed plugin always asked to upgrade
* [Updated April 16, 2011]

= 1.0 =
* First version
* [Created April 16, 2011]

== Upgrade Notice ==

= 1.5 =
* Fix bug that causes some Admin screens to not work

= 1.2 =
* Fixed compatability with WP-Scheduled-Styles (yet to be released)

= 1.1 =
* Fixed error where installed plugin always asked to upgrade