<?php
/**
 * Classic Editor
 *
 * Plugin Name: Classic Editor
 * Plugin URI:  https://wordpress.org
 * Description: Enables the WordPress classic editor and the old-style Edit Post screen layout (TinyMCE, meta boxes, etc.). Supports the older plugins that extend this screen.
 * Version:     1.0-beta
 * Author:      WordPress Contributors
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: classic-editor
 * Domain Path: /languages
 *
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( ! class_exists( 'Classic_Editor' ) ) :
class Classic_Editor {
	const plugin_version = 1.0;
	private static $settings;

	private function __construct() {}

	public static function init_actions() {
		$gutenberg = false;
		$block_editor = false;
		$post_id = 0;

		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );

		// Always remove the "Try Gutenberg" dashboard widget. See https://core.trac.wordpress.org/ticket/44635.
		remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );

		// Show warning on the post-upgrade screen (about.php).
		add_action( 'all_admin_notices', array( __CLASS__, 'notice_after_upgrade' ) );

		if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
			// Gutenberg is installed and activated.
			$gutenberg = true;
			$post_id = self::get_edited_post_id();
		}

		if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
			// Block editor.
			$block_editor = true;
		}

		if ( ! $gutenberg && ! $block_editor ) {
			return; // Nothing to do :)
		}

		$settings = self::get_settings();

		if ( ! $settings['hide-settings-ui'] ) {
			// Show the plugin's settings, and the link to them in the plugins list table.
			add_filter( 'plugin_action_links', array( __CLASS__, 'add_settings_link' ), 10, 2 );
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

			// User settings.
			add_action( 'personal_options_update', array( __CLASS__, 'save_user_settings' ) );
			add_action( 'profile_personal_options', array( __CLASS__, 'user_settings' ) );
		}

		if ( $block_editor && $settings['replace'] ) {
			// Consider disabling other block editor functionality.
			add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
		}

		if ( $gutenberg && ( $settings['replace'] || self::is_classic( $post_id ) ) ) {
			// gutenberg.php
			remove_action( 'admin_menu', 'gutenberg_menu' );
			remove_action( 'admin_notices', 'gutenberg_build_files_notice' );
			remove_action( 'admin_notices', 'gutenberg_wordpress_version_notice' );
			remove_action( 'admin_init', 'gutenberg_redirect_demo' );

			remove_filter( 'replace_editor', 'gutenberg_init' );

			// lib/client-assets.php
			remove_action( 'wp_enqueue_scripts', 'gutenberg_register_scripts_and_styles', 5 );
			remove_action( 'admin_enqueue_scripts', 'gutenberg_register_scripts_and_styles', 5 );
			remove_action( 'wp_enqueue_scripts', 'gutenberg_common_scripts_and_styles' );
			remove_action( 'admin_enqueue_scripts', 'gutenberg_common_scripts_and_styles' );

			// lib/compat.php
			remove_filter( 'wp_refresh_nonces', 'gutenberg_add_rest_nonce_to_heartbeat_response_headers' );

			// lib/rest-api.php
			remove_action( 'rest_api_init', 'gutenberg_register_rest_routes' );
			remove_action( 'rest_api_init', 'gutenberg_add_taxonomy_visibility_field' );

			remove_filter( 'rest_request_after_callbacks', 'gutenberg_filter_oembed_result' );
			remove_filter( 'registered_post_type', 'gutenberg_register_post_prepare_functions' );
			remove_filter( 'register_post_type_args', 'gutenberg_filter_post_type_labels' );

			// lib/meta-box-partial-page.php
			remove_action( 'do_meta_boxes', 'gutenberg_meta_box_save', 1000 );
			remove_action( 'submitpost_box', 'gutenberg_intercept_meta_box_render' );
			remove_action( 'submitpage_box', 'gutenberg_intercept_meta_box_render' );
			remove_action( 'edit_page_form', 'gutenberg_intercept_meta_box_render' );
			remove_action( 'edit_form_advanced', 'gutenberg_intercept_meta_box_render' );

			remove_filter( 'redirect_post_location', 'gutenberg_meta_box_save_redirect' );
			remove_filter( 'filter_gutenberg_meta_boxes', 'gutenberg_filter_meta_boxes' );
		}

		if ( $gutenberg && $settings['replace'] ) {
			// gutenberg.php
			remove_action( 'admin_init', 'gutenberg_add_edit_link_filters' );
			remove_action( 'admin_print_scripts-edit.php', 'gutenberg_replace_default_add_new_button' );

			remove_filter( 'body_class', 'gutenberg_add_responsive_body_class' );
			remove_filter( 'admin_url', 'gutenberg_modify_add_new_button_url' );

			// Keep
			// remove_filter( 'wp_kses_allowed_html', 'gutenberg_kses_allowedtags', 10, 2 ); // not needed in 5.0
			// remove_filter( 'bulk_actions-edit-wp_block', 'gutenberg_block_bulk_actions' );

			// lib/compat.php
			remove_action( 'admin_enqueue_scripts', 'gutenberg_check_if_classic_needs_warning_about_blocks' );

			// lib/register.php
			remove_action( 'edit_form_top', 'gutenberg_remember_classic_editor_when_saving_posts' );

			remove_filter( 'redirect_post_location', 'gutenberg_redirect_to_classic_editor_when_saving_posts' );
			remove_filter( 'get_edit_post_link', 'gutenberg_revisions_link_to_editor' );
			remove_filter( 'wp_prepare_revision_for_js', 'gutenberg_revisions_restore' );
			remove_filter( 'display_post_states', 'gutenberg_add_gutenberg_post_state' );

			// lib/plugin-compat.php
			remove_filter( 'wp_insert_post_data', 'gutenberg_remove_wpcom_markdown_support' );

			// Keep

			// lib/blocks.php
			// remove_filter( 'the_content', 'do_blocks', 9 );

			// Continue to disable wpautop inside TinyMCE for posts that were started in Gutenberg.
			// remove_filter( 'wp_editor_settings', 'gutenberg_disable_editor_settings_wpautop' );

			// Keep the tweaks to the PHP wpautop.
			// add_filter( 'the_content', 'wpautop' );
			// remove_filter( 'the_content', 'gutenberg_wpautop', 8 );

			// remove_action( 'init', 'gutenberg_register_post_types' );
		}

		if ( ! $settings['replace'] ) {
			// Menus
			add_action( 'admin_menu', array( __CLASS__, 'add_submenus' ) );

			// Row actions (edit.php)
			add_filter( 'page_row_actions', array( __CLASS__, 'add_edit_links' ), 15, 2 );
			add_filter( 'post_row_actions', array( __CLASS__, 'add_edit_links' ), 15, 2 );

			add_filter( 'get_edit_post_link', array( __CLASS__, 'get_edit_post_link' ) );
			add_filter( 'use_block_editor_for_post', array( __CLASS__, 'choose_editor' ), 100, 2 );
			add_filter( 'redirect_post_location', array( __CLASS__, 'redirect_location' ) );
			add_action( 'edit_form_top', array( __CLASS__, 'add_field' ) );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 2 );

			// TODO: needs https://github.com/WordPress/gutenberg/pull/12309 
			// add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_scripts' ) );

			if ( $settings['remember'] ) {
				add_action( 'edit_form_top', array( __CLASS__, 'remember_classic' ) );
				add_filter( 'block_editor_settings', array( __CLASS__, 'remember_block_editor' ), 10, 2 );
			}
		}
	}

	private static function get_settings( $update = 'no' ) {
		/**
		 * Can be used to override the plugin's settings and hide the settings UI.
		 *
		 * Has to return an associative array with (up to) three keys with boolean values.
		 * The defaults are:
		 *   'replace' => true,
		 *   'remember' => false,
		 *   'allow_users' => true,
		 *
		 * Note: using this filter always hides the settings UI (as it overrides the user's choices).
		 */
		$settings = apply_filters( 'classic_editor_plugin_settings', false );

		if ( is_array( $settings ) ) {
			// Normalize...
			return array(
				'replace' => ! empty( $settings['replace'] ),
				'remember' => ( empty( $settings['replace'] ) && ! empty( $settings['remember'] ) ),
				'allow-users' => ( ! isset( $settings['allow-users'] ) || $settings['allow-users'] ),
				'hide-settings-ui' => true,
			);
		}
		
		if ( ! empty( self::$settings ) && $update === 'no' ) {
			return self::$settings;
		}

		$use_defaults = true;
		$replace = true;
		$remember = false;

		if ( ( ! isset( $GLOBALS['pagenow'] ) || $GLOBALS['pagenow'] !== 'options-writing.php' ) && get_option( 'classic-editor-allow-users' ) !== 'disallow' ) {
			$user_id = 0;  // Allow admins to set a user's options?
			$option = get_user_option( 'classic-editor-settings', $user_id );

			if ( ! empty( $option ) ) {
				$use_defaults = false;

				if ( $option === 'remember' ) {
					$remember = true;
					$replace = false;
				} else {
					$remember = false;
					$replace = ( $option !== 'no-replace' );
				}
			}
		}

		if ( $use_defaults ) {
			$replace = get_option( 'classic-editor-replace' ) !== 'no-replace';
			$remember = ( ! $replace && get_option( 'classic-editor-remember' ) === 'remember' );
		}

		self::$settings = array(
			'replace' => $replace,
			'remember' => $remember,
			'hide-settings-ui' => false,
			'allow-users' => get_option( 'classic-editor-allow-users' ) !== 'disallow',
		);
		
		return self::$settings;
	}

	private static function is_classic( $post_id = 0 ) {
		if ( $post_id ) {
			$settings = self::get_settings();

			if ( $settings['remember'] && ! isset( $_GET['classic-editor__forget'] ) ) {
				$which = get_post_meta( $post_id, 'classic-editor-rememebr', true );
				// The editor choice will be remembered when the post is opened in either Classic or Block editor.
				if ( 'classic-editor' === $which ) {
					return true;
				} elseif ( 'block-editor' === $which ) {
					return false;
				}
			}
		}

		if ( isset( $_GET['classic-editor'] ) ) {
			return true;
		}

		return false;
	}

	private static function get_edited_post_id() {
		if (
			! empty( $_GET['post'] ) &&
			! empty( $_GET['action'] ) &&
			$_GET['action'] === 'edit' &&
			! empty( $GLOBALS['pagenow'] ) &&
			$GLOBALS['pagenow'] === 'post.php'
		) {
			return (int) $_GET['post']; // post_ID
		}

		return 0;
	}

	public static function register_settings() {
		// Add an option to Settings -> Writing
		register_setting( 'writing', 'classic-editor-replace', array(
			'sanitize_callback' => array( __CLASS__, 'validate_options' ),
		) );

		register_setting( 'writing', 'classic-editor-remember', array(
			'sanitize_callback' => array( __CLASS__, 'validate_options' ),
		) );

		register_setting( 'writing', 'classic-editor-allow-users', array(
			'sanitize_callback' => array( __CLASS__, 'validate_options_allow_users' ),
		) );

		add_option_whitelist( array(
			'writing' => array( 'classic-editor-replace', 'classic-editor-remember', 'classic-editor-allow-users' ),
		) );

		add_settings_field( 'classic-editor-1', __( 'Default editor for all users', 'classic-editor' ), array( __CLASS__, 'settings_1' ), 'writing' );
		add_settings_field( 'classic-editor-2', __( 'Open the last editor used for each post', 'classic-editor' ), array( __CLASS__, 'settings_2' ), 'writing' );
		add_settings_field( 'classic-editor-3', __( 'Allow users to switch editors', 'classic-editor' ), array( __CLASS__, 'settings_3' ), 'writing' );
	}

	public static function save_user_settings( $user_id ) {
		if (
			isset( $_POST['classic-editor-user-settings'] ) &&
			isset( $_POST['classic-editor-replace'] ) &&
			wp_verify_nonce( $_POST['classic-editor-user-settings'], 'allow-user-settings' )
		) {
			$user_id = (int) $user_id;

			if ( $user_id !== get_current_user_id() && ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

			$value = self::validate_options( $_POST['classic-editor-replace'] );

			if ( $value === 'no-replace' && $_POST['classic-editor-remember'] === 'remember' ) {
				$value = 'remember';

			}

			update_user_option( $user_id, 'classic-editor-settings', $value );
		}
	}

	/**
	 * Validate
	 */
	public static function validate_options( $value ) {
		if ( $value === 'no-replace' || $value === 'remember' ) {
			return $value;
		}

		return 'replace';
	}

	public static function validate_options_allow_users( $value ) {
		if ( $value === 'allow' ) {
			return 'allow';
		}

		return 'disallow';
	}

	public static function settings_1() {
		$settings = self::get_settings( 'update' );

		?>
		<div class="classic-editor-options">
			<label for="classic-editor-replace" class="screen-reader-text">
				<?php _e( 'Select default editor for all users', 'classic-editor' ); ?>
			</label>
			<select name="classic-editor-replace" id="classic-editor-replace">
				<option value="no-replace"><?php _e( 'Block Editor', 'classic-editor' ); ?></option>
				<option value="replace"<?php if ( $settings['replace'] ) echo ' selected'; ?>><?php _e( 'Classic Editor', 'classic-editor' ); ?></option>
			</select>
			<!--<p class="help"><?php _e( 'Includes optional links back to the Classic editor when the Block editor is selected.', 'classic-editor' ); ?></p>-->
		</div>
		<?php
	}

	public static function settings_2() {
		$settings = self::get_settings( 'update' );
		$disabled = $settings['replace'] ? ' disabled' : '';
		$padding = is_rtl() ? 'padding-left: 1em;' : 'padding-right: 1em;';

		?>
		<div class="classic-editor-options">
			<label style="<?php echo $padding ?>">
			<input type="radio" name="classic-editor-remember" id="classic-editor-remember" value="remember"<?php echo $disabled; if ( ! $disabled && $settings['remember'] ) echo ' checked'; ?> />
			<?php _e( 'Yes', 'classic-editor' ); ?>
			</label>

			<label style="<?php echo $padding ?>">
			<input type="radio" name="classic-editor-remember" id="classic-editor-no-remember" value="no-remember"<?php echo $disabled; if ( ! $disabled && ! $settings['remember'] ) echo ' checked'; ?> />
			<?php _e( 'No', 'classic-editor' ); ?>
			</label>
		</div>
		<script>
		jQuery( 'document' ).ready( function( $ ) {
			var select = $( '#classic-editor-replace' );

			if ( window.location.hash === '#classic-editor-options' ) {
				$( '.classic-editor-options' ).closest( 'td' ).addClass( 'highlight' );
			}
			select.on( 'change', function() {
				if ( select.find( ':selected' ).val() === 'replace' ) {
					$( 'input[name="classic-editor-remember"]' ).prop({ checked: false, disabled: true });
				} else {
					$( 'input[name="classic-editor-remember"]' ).prop({ disabled: false });
				}
			});
		} );
		</script>
		<?php
	}

	public static function settings_3() {
		$settings = self::get_settings( 'update' );
		$padding = is_rtl() ? 'padding-left: 1em;' : 'padding-right: 1em;';

		?>
		<div class="classic-editor-options">
			<label style="<?php echo $padding ?>">
			<input type="radio" name="classic-editor-allow-users" id="classic-editor-allow-users" value="allow"<?php if ( $settings['allow-users'] ) echo ' checked'; ?> />
			<?php _e( 'Yes', 'classic-editor' ); ?>
			</label>

			<label style="<?php echo $padding ?>">
			<input type="radio" name="classic-editor-allow-users" id="classic-editor-no-allow-users" value="no-allow"<?php if ( ! $settings['allow-users'] ) echo ' checked'; ?> />
			<?php _e( 'No', 'classic-editor' ); ?>
			</label>
		</div>
		<?php
	}
	
	/**
	 * Shown on the Profile page when allowed by admin.
	 */
	public static function user_settings() {
		global $user_can_edit;
		$settings = self::get_settings( 'update' );

		if (
			! defined( 'IS_PROFILE_PAGE' ) ||
			! IS_PROFILE_PAGE ||
			! $user_can_edit ||
			! $settings['allow-users']
		) {
			return;
		}

		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Editor', 'classic-editor' ); ?></th>
				<td>
				<?php wp_nonce_field( 'allow-user-settings', 'classic-editor-user-settings' ); ?>
				<?php self::settings_1(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Open the last editor used for each post', 'classic-editor' ); ?></th>
				<td>
				<?php self::settings_2(); ?>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function notice_after_upgrade() {
		global $pagenow;
		$settings = self::get_settings();

		if ( $pagenow !== 'about.php' || $settings['hide-settings-ui'] || ! $settings['replace'] ) {
			// No need to show when the settings are preset from another plugin or when not replacing the Block Editor.
			return;
		}

		$message = __( 'The Classic Editor plugin prevents use of the new Block Editor.', 'classic-editor' );

		if ( $settings['allow-users'] && current_user_can( 'edit_posts' ) ) {
			$message .= ' ' . sprintf( __( 'Change the %1$sClassic Editor settings%2$s on your User Profile page.', 'classic-editor' ), '<a href="profile.php#classic-editor-options">', '</a>' );
		} elseif ( current_user_can( 'manage_options' ) ) {
			$message .= ' ' . sprintf( __( 'Change the %1$sClassic Editor settings%2$s.', 'classic-editor' ), '<a href="options-writing.php#classic-editor-options">', '</a>' );
		}

		?>
		<div id="message" class="error notice" style="display: block !important">
			<p><?php echo $message; ?></p>
		</div>
		<?php
	}

	/**
	 * Add a hidden field in edit-form-advanced.php
	 * to help redirect back to the classic editor on saving.
	 */
	public static function add_field() {
		?>
		<input type="hidden" name="classic-editor" value="" />
		<?php
	}

	/**
	 * Remember when the Classic editor was used to edit a post.
	 */
	public static function remember_classic( $post ) {
		if ( ! empty( $post->ID ) ) {
			self::remember( $post->ID, 'classic-editor' );
		}
	}

	public static function remember_block_editor( $editor_settings, $post ) {
		if ( ! empty( $post->ID ) ) {
			self::remember( $post->ID, 'block-editor' );
		}

		return $editor_settings;
	}

	private static function remember( $post_id, $editor ) {
		if ( use_block_editor_for_post_type( get_post_type( $post_id ) ) ) {
			if ( get_post_meta( $post_id, 'classic-editor-rememebr', true ) !== $editor ) {
				update_post_meta( $post_id, 'classic-editor-rememebr', $editor );
			}
		}
	}

	public static function choose_editor( $which_editor, $post ) {
		// Open the Block editor when no $post and for "Add New" links.
		if ( empty( $post->ID ) || ( $post->post_status === 'auto-draft' && ! self::is_classic() ) ) {
			return $which_editor;
		}

		if ( self::is_classic( $post->ID ) ) {
			return false;
		}

		return $which_editor;
	}

	/**
	 * Keep the `classic-editor` query arg through redirects when saving posts.
	 */
	public static function redirect_location( $location ) {
		if (
			isset( $_REQUEST['classic-editor'] ) ||
			( isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], '&classic-editor' ) !== false )
		) {
			$location = add_query_arg( 'classic-editor', '', $location );
		}

		return $location;
	}

	/**
	 * Keep the `classic-editor` query arg when looking at revisions.
	 */
	public static function get_edit_post_link( $url ) {
		if ( isset( $_REQUEST['classic-editor'] ) ) {
			$url = add_query_arg( 'classic-editor', '', $url );
		}

		return $url;
	}

	public static function add_meta_box( $post_type, $post ) {
		if ( ! self::is_classic( $post->ID ) || ! use_block_editor_for_post_type( $post_type ) ) {
			return;
		}

		$id = 'classic-editor-switch-editor';
		$title = __( 'Editor', 'classic-editor' );
		$callback = array( __CLASS__, 'do_meta_box' );
		$args = array(
			'__back_compat_meta_box' => true,
	    );

		add_meta_box( $id, $title, $callback, null, 'side', 'default', $args );
	}

	public static function do_meta_box( $post ) {
		$edit_url = get_edit_post_link( $post->ID, 'raw' );
		$settings = self::get_settings();

		$edit_url = remove_query_arg( 'classic-editor', $edit_url );

		if ( $settings['remember'] ) {
			// Forget the previous value when going to a specific editor.
			$edit_url = add_query_arg( 'classic-editor__forget', '', $edit_url );
		}

		?>
		<p>
			<label class="screen-reader-text" for="classic-editor-switch-editor"><?php _e( 'Select editor' ); ?></label>
			<select id="classic-editor-switch-editor" style="width: 100%;max-width: 20em;">
				<option value=""><?php _e( 'Classic Editor', 'classic-editor' ); ?></option>
				<option value="" data-url="<?php echo esc_url( $edit_url ); ?>"><?php _e( 'Block Editor', 'classic-editor' ); ?></option>
			</select>
		</p>
		<script>
		jQuery( 'document' ).ready( function( $ ) {
			var $select = $( '#classic-editor-switch-editor' );
			$select.on( 'change', function( event ) {
				var url = $select.find( ':selected' ).attr( 'data-url' );
				if ( url ) {
					document.location = url;
				}
			} );
		} );
		</script>
		<?php
	}

	public static function enqueue_scripts() {
		wp_enqueue_script(
			'classic-editor-add-submenu',
			plugins_url( 'js/block-editor-plugin.js', __FILE__ ),
			array( 'wp-element', 'wp-components', 'lodash' ),
			self::plugin_version,
			true
		);

		wp_localize_script(
			'classic-editor-add-submenu',
			'classicEditorPluginL10n',
			array( 'linkText' => __( 'Switch to Classic Editor', 'classic-editor' ) )
		);
	}

	/**
	 * Add an `Add New (Classic)` submenu for Posts, Pages, etc.
	 */
	public static function add_submenus() {
		foreach ( get_post_types( array( 'show_ui' => true ) ) as $type ) {
			$type_obj = get_post_type_object( $type );

			if ( ! $type_obj->show_in_menu || ! use_block_editor_for_post_type( $type ) ) {
				continue;
			}

			if ( $type_obj->show_in_menu === true ) {
				if ( 'post' === $type ) {
					$parent_slug = 'edit.php';
				} elseif ( 'page' === $type ) {
					$parent_slug = 'edit.php?post_type=page';
				} else {
					// Not for a submenu.
					continue;
				}
			} else {
				$parent_slug = $type_obj->show_in_menu;
			}

			$item_name = $type_obj->labels->add_new . ' ' . __( '(Classic)', 'classic-editor' );
			$path = "post-new.php?post_type={$type}&classic-editor";
			add_submenu_page( $parent_slug, $type_obj->labels->add_new, $item_name, $type_obj->cap->edit_posts, $path );
		}
	}

	/**
	 * Add a link to the settings on the Plugins screen.
	 */
	public static function add_settings_link( $links, $file ) {
		$settings = self::get_settings();

		if ( $file === 'classic-editor/classic-editor.php' && ! $settings['hide-settings-ui'] && current_user_can( 'manage_options' ) ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-writing.php#classic-editor-options' ), __( 'Settings', 'classic-editor' ) );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Adds links to the post/page screens to edit any post or page in
	 * the Classic editor.
	 *
	 * @param  array   $actions Post actions.
	 * @param  WP_Post $post    Edited post.
	 *
	 * @return array   Updated post actions.
	 */
	public static function add_edit_links( $actions, $post ) {
		// This is in Gutenberg, don't duplicate it.
		if ( array_key_exists( 'classic', $actions ) ) {
			unset( $actions['classic'] );
		}

		if ( ! array_key_exists( 'edit', $actions ) ) {
			return $actions;
		}

		$edit_url = get_edit_post_link( $post->ID, 'raw' );

		if ( ! $edit_url ) {
			return $actions;
		}

		$settings = self::get_settings();

		if ( $settings['remember'] ) {
			// Forget the previous value when going to a specific editor.
			$edit_url = add_query_arg( 'classic-editor__forget', '', $edit_url );
		}

		// Build the edit actions. See also: WP_Posts_List_Table::handle_row_actions().
		$title = _draft_or_post_title( $post->ID );

		// Link to the Block editor.
		$text = __( 'Block editor', 'classic-editor' );
		/* translators: %s: post title */
		$label = sprintf( __( 'Edit &#8220;%s&#8221; in the Block editor', 'classic-editor' ), $title );
		$edit_block = sprintf( '<a href="%s" aria-label="%s">%s</a>', esc_url( $edit_url ), esc_attr( $label ), $text );

		// Link to the Classic editor.
		$url = add_query_arg( 'classic-editor', '', $edit_url );
		$text = __( 'Classic editor', 'classic-editor' );
		/* translators: %s: post title */
		$label = sprintf( __( 'Edit &#8220;%s&#8221; in the Classic editor', 'classic-editor' ), $title );
		$edit_classic = sprintf( '<a href="%s" aria-label="%s">%s</a>', esc_url( $url ), esc_attr( $label ), $text );

		$edit_actions = array(
			'classic-editor-block' => $edit_block,
			'classic-editor-classic' => $edit_classic,
		);

		// Insert the new Edit actions instead of the Edit action.
		$edit_offset = array_search( 'edit', array_keys( $actions ), true );
		array_splice( $actions, $edit_offset, 1, $edit_actions );

		return $actions;
	}

	/**
	 * Set defaults on activation.
	 */
	public static function activate() {
		if ( ! get_option( 'classic-editor-replace' ) ) {
			update_option( 'classic-editor-replace', 'replace' );
			update_option( 'classic-editor-remember', '' );
			update_option( 'classic-editor-allow-users', 'allow' );
		}
	}

	/**
	 * Delete the options on deactivation.
	 */
	public static function deactivate() {
		delete_option( 'classic-editor-replace' );
		delete_option( 'classic-editor-remember' );
		delete_option( 'classic-editor-allow-users' );
	}
}

add_action( 'plugins_loaded', array( 'Classic_Editor', 'init_actions' ), 20 );

endif;
