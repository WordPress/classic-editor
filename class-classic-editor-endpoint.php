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
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/
	public function get_post_settings( $request ) {
		$post_id = $request['id'];
		$settings = Classic_Editor::get_settings();

		$use_block_editor = 'block' === $settings['editor'];
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_REST_Response( array( 'selected_editor' => $settings['editor'] ), 200 );
		}

		$editor = Classic_Editor::choose_editor( $use_block_editor, $post ) ? 'block' : 'classic';

		return new WP_REST_Response( array( 'selected_editor' => $editor ), 200 );
	}
}