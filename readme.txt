=== Collaborate Notes ===
Contributors: glennsjostrom
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=glenn.sjostrom@gmail.com
Author URI: http://glennsjostrom.com
Tags: to-do, to do list, to-do list, admin, list, todo, to do, task, notes, collaborate, javascript, html5, backbone, share, simple, ajax
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight notes and tasks management. 
Share important notes and tasks with your webmaster, clients and users.

== Description ==

This plugin allows you to create, share and set reminder for notes and tasks.

Collaborate Notes aims to make it easier, especially for the client, to notify and collaborate with the webmaster/admin without using email.

Inspired by Google Keep.


= Features =

* Add/edit/delete/complete notes
* Set own reminder for notes, sending reminder through email
* Assigne notes to multiple users
* Assigned user will be notified by email
* Mark notes as completed. Similar to a to-do list
* Choose if you want to notify assigned users after editing note
* Log will display last edit by user and time

= Coming soon =
* Media upload connected to the Wordpress Media Upload.

== Installation ==

1. Upload the folder `/collaborate-notes/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Open "Notes (0)" in the admin toolbar.

== Frequently Asked Questions ==

= Is there any way i can propose new feature? =
Yes, write to me at glenn.sjostrom@gmail.com

= Why is the plugin not sending any email? =
This issue is not about the plugin. It's probably about your host mail settings.
You should try install WP-Mail-SMTP, for example. Then get your SMTP settings from your
host and configure them with the plugin.

== Screenshots ==

1. Creating first note and assigne admin, you can choose to assign multiple users.
2. Set own reminder for note.
3. After clicked on "Remind me".
4. Showing notify alert after adding note.
5. Assigned users gets email.
6. Logged in as "admin". Showing assigned note.
7. When editing note you get alert if you want to notify assigned users.
8. If you click on "Yes, send notify" getting success alert.

== Changelog ==

= 1.0.4 =
* Close application with Escape-button
* Fix "Screen options" bug.

= 1.0.3 =
* Fixed is_empty typo/bug

= 1.0.2 =
* Fixed z-index bug for WP 4.0

= 1.0.1 =
* Fixed "debug.log" messages.
* Fixed activation error message "Headers Already Sent Message".

= 1.0.0 =
* First release

== Upgrade Notice ==

= 1.0.2 =
* Bug fix for WP 4.0

= 1.0.1 =
* Fixed some bugs

= 1.0.0 =
* Initial submittion to the WordPress.org repository
