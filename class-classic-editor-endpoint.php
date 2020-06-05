<?php

class Classic_Editor_Endpoint extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'classic-editor/v' . $version;

		register_rest_route( $namespace, '/user-settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'permission_callback' => array( $this, 'get_user_settings_permissions_check' ),
			) ) );

		register_rest_route( $namespace, '/post-settings/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_post_settings' ),
				'permission_callback' => array( $this, 'get_user_settings_permissions_check' ),
			) ) );
  	}
 
	/**
	 * Check if a given request has access to the user's Classic Editor plugin settings.
	 * 
	 * @return true|WP_Error
	 */
	public function get_user_settings_permissions_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Get Classic Editor plugin settings for current user
	 * 
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_user_settings() {
		$settings = Classic_Editor::get_settings();

		$response = array(
			'can_switch_editors' => $settings['allow-users'],
			'selected_editor'    => $settings['editor'],
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get Classic Editor plugin settings for a specific post
	 * 
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_post_settings( $request ) {
		$post_id     = $request['id'];
		$settings    = Classic_Editor::get_settings();
		$user_editor = $settings['editor'];

		$editor = $this->get_editor_for_post( $post_id, $user_editor );

		return new WP_REST_Response( array( 'selected_editor' => $editor ), 200 );
	}


	/**
	 * Get the editor to be used to edit a post.
	 * 
	 * @param  WP_Post $post        The post object.
	 * @param  string  $user_editor The user's editor preference.
	 * @return string
	 */
	private function get_editor_for_post( $post_id, $user_editor ) {
		$post = get_post( $post_id );

		// If the post doesn't exist, return the user's setting.
		if ( ! $post ) {
			return $user_editor;
		}

		// Get the editors supported by the post.
		$post_type = get_post_type( $post );
		$editors   = array(
			'classic_editor' => post_type_supports( $post_type, 'editor' ),
			'block_editor'   => post_type_supports( $post_type, 'editor' ) && apply_filters( 'use_block_editor_for_post_type', true, $post_type ),
		);
		$editors   = apply_filters( 'classic_editor_enabled_editors_for_post_type', $editors, $post_type );
		$editors   = apply_filters( 'classic_editor_enabled_editors_for_post', $editors, $post );

		// If the post doesn't support either editor, return the user's setting.
		if ( ! $editors['block_editor'] && ! $editors['classic_editor'] ) {
			return $user_editor;
		}

		// Check if the post should be edited with the Classic Editor.
		$use_classic = Classic_Editor::is_classic( $post_id );

		// Avoid using an editor if the post doesn't support it.
		if ( $use_classic && ! $editors['classic_editor'] ) {
			return 'block';
		}
		if ( ! $use_classic && ! $editors['block_editor'] ) {
			return 'classic';
		}

		return $use_classic ? 'classic' : 'block';
	}
}
