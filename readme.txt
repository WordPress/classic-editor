=== Classic Editor ===
Contributors: azaozz, melchoyce, chanthaboune, alexislloyd, pento, youknowriad, desrosj, luciano-croce
Tags: editor, classic editor, block editor, gutenberg
Requires at least: 4.9
Tested up to: 5.0
Stable tag: 1.1
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables the previous "classic" editor and the old-style Edit Post screen with TinyMCE, Meta Boxes, etc. Supports all plugins that extend this screen.

== Description ==

Classic Editor is an official plugin maintained by the WordPress team that restores the previous ("classic") WordPress editor and the "Edit Post" screen. It makes it possible to use plugins that extend that screen, add old-style meta boxes, or otherwise depend on the previous editor.
By default, this plugin hides all functionality available in the new Block Editor ("Gutenberg").

At a glance, this plugin adds the following:

* Administrators can select the default editor for all users.
* Administrators can allow users to change their default editor.
* When allowed, the users can choose which editor to use for each post.
* Each post opens in the last editor used regardless of who edited it last. This is important for maintaining a consistent experience when editing content.

The Classic Editor plugin supports WordPress version 4.9, so it can be installed and configured before WordPress is upgraded to version 5.0. In this case, only the admin settings are visible.

In addition, the Classic Editor plugin includes several filters that let other plugins control the settings, and the editor choice per post and per post type.

Classic Editor is an official WordPress plugin, and will be maintained until at least 2022.

== Changelog ==

= 1.1 =
Fixed a bug where it may attempt to load the Block Editor for post types that do not support editor when users are allowed to switch editors.

= 1.0 =
Updated for WordPress 5.0.
Changed all "Gutenberg" names/references to "Block Editor".
Refreshed the settings UI.
Removed disabling of the Gutenberg plugin. This was added for testing in WordPress 4.9. Users who want to continue following the development of Gutenberg in WordPress 5.0 and beyond will not need another plugin to disable it.
Added support for per-user settings of default editor.
Added support for admins to set the default editor for the site.
Added support for admins to allow users to change their default editor.
Added support for network admins to prevent site admins from changing the default settings.
Added support to store the last editor used for each post and open it next time. Enabled when users can choose default editor.
Added "post editor state" in the listing of posts on the Posts screen. Shows the editor that will be opened for the post. Enabled when users can choose default editor.
Added `classic_editor_enabled_editors_for_post` and `classic_editor_enabled_editors_for_post_type` filters. Can be used by other plugins to control or override the editor used for a particular post of post type.
Added `classic_editor_plugin_settings` filter. Can be used by other plugins to override the settings and disable the settings UI.

= 0.5 =
Updated for Gutenberg 4.1 and WordPress 5.0-beta1.
Removed some functionality that now exists in Gutenberg.
Fixed redirecting back to the CLassic editor after looking at post revisions.

= 0.4 =
Fixed removing of the "Try Gutenberg" call-out when the Gutenberg plugin is not activated.
Fixed to always show the settings and the settings link in the plugins list table.
Updated the readme text.

= 0.3 =
Updated the option from a checkbox to couple of radio buttons, seems clearer. Thanks to @designsimply for the label text suggestions.
Some general updates and cleanup.

= 0.2 =
Update for Gutenberg 1.9.
Remove warning and automatic deactivation when Gutenberg is not active.

= 0.1 =
Initial release.

== Screenshots ==
1. Admin settings on the Settings -> Writing screen.
2. User settings on the Profile screen. Visible when the users are allowed to switch editors.
3. "Action links" to choose alternative editor. Visible when the users are allowed to switch editors.
4. Link to switch to the Block Editor while editing a post in the Classic Editor. Visible when the users are allowed to switch editors.
5. Link to switch to the Classic Editor while editing a post in the Block Editor. Visible when the users are allowed to switch editors.
6. Network setting to allow site admins to change the default options.
