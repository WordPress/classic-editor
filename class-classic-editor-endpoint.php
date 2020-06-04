<?php

class Classic_Editor_Endpoint extends WP_REST_Controller {
	/**
	* Register the routes for the objects of the controller.
	*/
	public function register_routes() {
		$version = '1';
		$namespace = 'classic-editor/v' . $version;

		register_rest_route( $namespace, '/settings/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
				'args'                => array(
				'id'     => array(
					'description'       => 'ID of user to get settings for.',
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
            	)),
			) ) );
  	}
 
	/**
	* Get classic editor plugin settings for site
	* 
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/
	public function get_user_settings( $request ) {
		$user_id = [ 'user_id' => $request['id']];
		$settings = Classic_Editor::get_user_settings( $user_id );

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	* Check if a given request has access to get user settings
	*
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|bool
	*/
	public function get_settings_permissions_check( $request ) {
		return true; 
  	}
}