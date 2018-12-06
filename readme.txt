=== Classic Editor ===
Contributors: azaozz, melchoyce, chanthaboune, alexislloyd, pento, youknowriad, desrosj, luciano-croce
Tags: editor, classic-editor, block-editor, gutenberg-editor, azaozz
Requires at least: 4.9
Tested up to: 5.0
Stable tag: 0.5
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Classic Editor restores the previous "classic" editor and the "edit post" screen. By default hides all functionality in the new Block Editor.

== Description ==
The Classic Editor is an official plugin maintained by the WordPress Team Contributors that restores the previous ("classic") editor and the "edit post" screen in core, and will be maintained until at least 2022.

It makes possible use the plugins (add-ons) that extend this screen, add old-style meta boxes, or otherwise depend on the previous editor-styling. By default, this plugin hides all functionality available in the new Block Editor (codename "Gutenberg"). This is important for maintaining a consistent experience when editing content.

At a glance, this plugin adds the following:

* Administrators can select the default editor for all users.
* Administrators can allow users to change their default editor.
* When allowed, the users can choose which editor to use for each post and post type.
* Each post type opens in the last editor used regardless of who edited it last.

The Classic Editor plugin supports WordPress 4.9+ so it can be installed and configured before WordPress is upgraded to version 5.0+ In this case, only the admin settings are visible.

In addition, the Classic Editor plugin includes several filters that let other plugins (add-ons) control the settings, and the editor choice per post and per post type.

== Screenshots ==
1. Admin Settings -> Writing Settings screen. Visible when the users have manage options capability.
2. Users Settings -> Profile -> Personal Options screen. Visible when the users are allowed to switch editors.
3. "Action links" to choose alternative editor. Visible when the users are allowed to choose editors.
4. "Action links" to switch to the Block Editor while editing a post in the Classic Editor. Visible when the users are allowed to choose editors.
5. "Action links" to switch to the Classic Editor while editing a post in the Block Editor. Visible when the users are allowed to choose editors.
6. Network setting to allow site admins to change the default option for all users. Visible when the users have manage options capability.

== Changelog ==
Detailed changes, updates, issues, pull requests and beta version are available on [GitHub](https://github.com/WordPress/classic-editor/).

= 1.0 =
* Updated for WordPress 5.0+
* Changed all "Gutenberg" Names/References to "Block Editor".
* Refreshed the settings UI.
* Removed disabling of the Gutenberg plugin. This was added for testing in WordPress 4.9. Users who want to continue following the development of Gutenberg in WordPress 5.0 and beyond will not need another plugin to disable it.
* Added support for per-user settings of default editor.
* Added support for admins to set the default editor for the site.
* Added support for admins to allow users to change their default editor.
* Added support for network admins to prevent site admins from changing the default settings.
* Added support to store the last editor used for each post and open it next time. Enabled when users can choose default editor.
* Added "post editor state" in the listing of posts on the Posts screen. Shows the editor that will be opened for the post. Enabled when users can choose default editor.
* Added `classic_editor_enabled_editors_for_post` and `classic_editor_enabled_editors_for_post_type` filters. Can be used by other plugins to control or override the editor used for a particular post of post type.
* Added `classic_editor_plugin_settings` filter. Can be used by other plugins to override the settings and disable the settings UI.

= 0.5 =
* Updated for Gutenberg 4.1 and WordPress 5.0-beta1.
* Removed some functionality that now exists in Gutenberg Editor.
* Fixed redirecting back to the CLassic Editor after looking at post revisions.

= 0.4 =
* Fixed removing of the "Try Gutenberg" call-out when the Gutenberg plugin is not activated.
* Fixed to always show the settings and the settings link in the plugins list table.
* Updated the readme text.

= 0.3 =
* Updated the option from a checkbox to couple of radio buttons, seems clearer. Thanks to @designsimply for the label text suggestions.
* Some general updates and cleanup.

= 0.2 =
* Update for Gutenberg 1.9.
* Remove warning and automatic deactivation when Gutenberg is not active.

= 0.1 =
* Initial release.

== Upgrade Notice ==
= 1.0 =
Compatible with WordPress 5.0+ ~ 4.9+ - General refactoring, interface enhancements, multisite improvements and bug fixes.
