<?php

class Classic_Editor_Endpoint extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'classic-editor/v' . $version;

		register_rest_route( $namespace, '/settings/(?P<user_id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'permission_callback' => array( $this, 'get_user_settings_permissions_check' ),
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
	* Get Classic Editor plugin settings for a given user id
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

	/**
	* Check if a given request has access to the user's Classic Editor plugin settings.
	* 
	* @param WP_REST_Request $request Full data about the request.
	* @return true|WP_Error
	*/
	public function get_user_settings_permissions_check( $request ) {
		$error = new WP_Error(
			'rest_user_invalid_id',
			__( 'Invalid user ID.' ),
			array( 'status' => 404 )
		);

		$user_id = $request['user_id'];
		$user = get_userdata( (int) $id );
		if ( empty( $user ) || ! $user->exists() ) {
			return $error;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user->ID ) ) {
			return $error;
		}

		if ( get_current_user_id() === $user->ID ) {
			return true;
		}

		if ( current_user_can( 'list_users' ) ) {
			return true;
		}

		return $error;
	}
}