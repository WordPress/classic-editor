=== Classic Editor ===
Contributors: azaozz
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restores the Classic Editor and the old-style Edit Post screen layout (TinyMCE, Meta boxes, etc.). Supports the plugins that extend this screen.

== Description ==

<strong>Warning: This is beta software, do not run on production sites!</strong>

Requires WordPress 4.9-beta2 or newer and Gutenberg plugin 1.5 or newer.

Classic Editor restores the previous Edit Post screen and makes it possible to use the WordPress plugins that extend it, add old-style meta boxes, or otherwise depend on the previous editor.

It has two modes:

1. Fully replaces the Gutenberg editor and restores the Edit Post template.
2. Adds alternate "Edit" links to the Posts and Pages screens, on the toolbar at the top of the screen, and in the admin menu. Using these links will open the corresponding post or page in the Classic Editor.

The modes can be changed from the Settings -> Writing screen. See the screenshots.

The current release is intended for testing with the [Gutenberg plugin](https://wordpress.org/plugins/gutenberg/) version 1.5 or newer.

== Changelog ==
= 0.3 =
Updated the option from a checkbox to couple of radio buttons, seems clearer. Thanks to @designsimply for the label text suggestions.
Some general updates and cleanup.

= 0.2 =
Update for Gutenberg 1.9.
Remove warning and automatic deactivation when Gutenberg is not active.

= 0.1 =
Initial release.

== Screenshots ==
1. The plugin settings are on the Settings -> Writing screen.
2. Link to edit the item using the Classic Editor on the Posts screen. Visible when the option to fully replace the editor is turned off.
3. Link to use the Classic Editor on the Admin toolbar. Shown on the Edit Post screen and on the front end when a single post is displayed. Visible when the option to fully replace the editor is turned off.
