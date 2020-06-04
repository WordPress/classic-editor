<?php

class Classic_Editor_Endpoint extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'classic-editor/v' . $version;

		register_rest_route( $namespace, '/settings/(?P<user_id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'args'                => array(
				'user_id'     => array(
					'description'       => 'ID of user to get settings for.',
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
            	)),
			) ) );
  	}
 
	/**
	* Get classic editor plugin settings for a given user id
	* 
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/
	public function get_user_settings( $request ) {
		$user_id = $request['user_id'];
		$settings = Classic_Editor::get_settings( 'yes', $user_id );

		$response = array(
			'can_switch_editors' => $settings['allow-users'],
			'selected_editor'    => $settings['editor'],
		);

		return new WP_REST_Response( $response, 200 );
	}
}