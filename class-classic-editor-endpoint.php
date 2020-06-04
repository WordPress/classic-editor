<?php

class Classic_Editor_Endpoint extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'classic-editor/v' . $version;

		register_rest_route( $namespace, '/settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'permission_callback' => array( $this, 'get_user_settings_permissions_check' ),
			) ) );
  	}
 
	/**
	* Check if a given request has access to the user's Classic Editor plugin settings.
	* 
	* @param WP_REST_Request $request Full data about the request.
	* @return true|WP_Error
	*/
	public function get_user_settings_permissions_check( $request ) {
		return get_current_user_id() > 0;
	}

	/**
	* Get Classic Editor plugin settings for a given user id
	* 
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/
	public function get_user_settings( $request ) {
		$settings = Classic_Editor::get_settings();

		$response = array(
			'can_switch_editors' => $settings['allow-users'],
			'selected_editor'    => $settings['editor'],
		);

		return new WP_REST_Response( $response, 200 );
	}
}