=== Basic Dev Tools ===
Contributors: rodtech
Donate link: http://marsminds.com
Tags: developer, development, tools, basic tools, cron, manager, settings
Requires at least: 3.0.1
Tested up to: 4.2
Stable tag: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin with some Basic Tools For Development and Developers.
Trying to be easier the way of develop common things in WP

== Description ==

This was developed thinking in the common things that a developer could need at the time of build an app in WP.

= Special Settings =

A Special Setting Page allowing you to easily change global settings that in ocations are hidden in WP.

= Cron Task and Schedules Manager =

A section that allow you to create and delete schedules and tasks that run internally in the WP Cron. You could see and monitor the distinct schedules, the times and next executions for each one. Also you have the posibilty to execute one of them without affect the cron schedules.

= Post Types Manager with shotcodes =

A section that allow you to create distinct Post Types without a line of code. You could specify names and special settings like new taxonomies for each one of them. Then you could call them with the common functions for posts from WP or using special shortcodes prepared for you to manage it.

**<a href="https://github.com/rodrigo-techera/basic-dev-tools">Fork it on Github</a>**

== Installation ==

1. Download Basic Dev Tools.
1. Upload the 'basic-dev-tools' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
1. Activate Basic Dev Tools from your Plugins page.

A new menu appears allowing you to use the tools.

== Frequently Asked Questions ==

None.

== Screenshots ==

1. On activation, the plugin adds a new menu with the available tools to use.
2. Special Settings page.
3. Task Manager Section in Cron Manager Page.
4. Schedules Manager Section in Cron Manager Page.
5. Post Types Manager Page with some items.

== Upgrade Notice ==

= 1.4.1 =
* New Special Settings added

== Changelog ==

= 1.4.1 (2015-05-24) =
* Added "Hide Admin Bar" Special Setting
* Added "Disable Theme Updates" Special Setting
* Added "Disable Plugin Updates" Special Setting
* Added "Disable Core Updates" Special Setting
* Screenshot changed

= 1.4 (2015-05-24) =
* Bug with files in svn fixed

= 1.3 (2015-05-23) =
* tableobject api updated
* Added Crons Manager Functionality with posibilty of create and execute tasks
* Added Cron Schedules to global wp filter
* Added Special Settings to manage common thing faster
* Screenshots added

= 1.2 (2015-05-03) =
* tableobject api updated
* Added Cron Task Manager menu
* Added Cron Schedules Manager
* Added partialy Cron Manager to implement in version 2
* New tags added to be relevant in the plugin
* Readme file updated

= 1.1 (2015-04-26) =
* tableobject api updated
* Shortcodes with dynamic options in function of the dynamic postypes
* Removed Cron Task Manager menu to implement in version 2

= 1.0 =
* Initial Release