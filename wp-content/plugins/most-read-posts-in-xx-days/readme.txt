=== Most Read Posts in XX days ===
Contributors: mrbrown
Donate link: http://www.studio404.it/2006/plugin-wordpress-articoli-piu-letti.html
Tags: stats, hits, visits, count, posts
Requires at least: 2.6.3
Tested up to: 3.9
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a simple plugin that counts and shows hits for each Post in your WordPress blog.

== Description ==

Most Read Posts counts each post reading and allows you to show the hits in:

* The Single Post page
* Index and Archive pages
* The Sidebar through a configurable Widget, also with posts' featured images

== Installation ==

1. Upload the plugin file and the `most-read-plugin` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. If needed, configure Settings and the Widget
1. Place these tags in your template:

* `single.php`

	`<?php ST4_single_hits(); ?>`
		
* `index.php` (and / or `archive.php` etc...), in the Loop

	`<?php ST4_hits(); ?>`

* `sidebar.php` (only if you don't use Widgets)
	
	`<?php`
`// last 5 posts in last 30 days, show the hits, show image preview
	ST4_mostread(30, 5, 'yes', 'yes');`
`// show 10 posts from all published posts, show hits, don't show preview
	ST4_mostread(0, 10, 'yes', 'no');`
`// show 3 pages from all published pages, show hits, don't show preview
	ST4_mostread(0, 10, 'yes', 'no', 'page');`
`?>`

== Frequently Asked Questions ==

= Can I show the most read Posts without the date limit? =

Yes, you can remove the date limit in the Sidebar widget settings

= Can I display Posts featured images? =

Yes, you can display them through Sidebar widget settings

= Can I track also Pages? =

Yes, since 2.3.3 version you can show Page hits through Sidebar widget settings

== Screenshots ==

1. noscreenshot

== Changelog ==

= 2.3.2 =
* Added hits count also for Pages.

= 2.3.1 =
* Added a sortable column with Posts' Hits in All Posts Administration Screen.

= 2.3 =
* Some Bugs fixed
* Added new Widget option to show featured image preview
* Added new Widget options to remove the date filter
* Added new Plugin Option to show post hits automatically
* Added new Plugin Option to use custom CSS rules
* Added new Plugin Option to set image preview custom CSS sizes
* Changes in localization files

= 2.2 =
* Code upgrade for WordPress 2.8 compatibility.
* Added new function `ST4_get_post_hits()` to get post hits outside the Loop
* Changes in localization files
* Code optimization

= 2.1 =
* First release.

== Upgrade Notice ==

= 2.3 =
Visit Plugin Settings Page and set a width and an height for image previews.

= 1.0 =
Delete old plugin directory and replace with the new one.