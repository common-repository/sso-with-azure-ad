<?php
/**
 * Plugin Callback API
 *
 * @author Justin Greer <justin@justin-greer.com>
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
 * WordPress Action azure_before_azure_auth_api
 */
do_action( 'azure_before_azure_auth_api', array( $_REQUEST ) );

/*
 * Plugin Settings
 */
$settings = get_option( 'azure_authenticate' );

/*
 * Gather the query
 */
global $wp_query;
$method = $wp_query->get( 'azure-auth' );

/*
 * Trigger SSO Azure AD Auth Call
 * Note this is a consent prompt
 */
if ( $method == 'trigger' ) {
	wp_redirect( 'https://login.microsoftonline.com/' . $settings['directory_id'] . '/oauth2/authorize/' . '?client_id=' . $settings['application_id'] . '&response_type=code&response_mode=query&redirect_uri=' . site_url( 'azure-auth/callback' ) . '&prompt=consent' );
	exit;
}

/*
 * Callback Method
 */
if ( $method == 'callback' ) {

	if ( isset( $_REQUEST['code'] ) ) {
		$post_data = array(
			'grant_type'    => 'authorization_code',
			'code'          => sanitize_text_field( $_REQUEST['code'] ),
			'redirect_uri'  => site_url( '/azure-auth/callback' ),
			'client_id'     => $settings['application_id'],
			'client_secret' => $settings['application_secret'],
			'resource'      => $settings['application_id'],
			'scope'         => 'user.read'
		);

		$response = wp_remote_post( 'https://login.microsoftonline.com/' . $settings['directory_id'] . '/oauth2/token/', array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => $post_data,
				'cookies'     => array()
			)
		);

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );

		if ( isset( $response->error_description ) ) {

			// @todo add logic to options
			wp_die( $response->error_description );
		}

		$user_info = json_decode( base64_decode( str_replace( '_', '/', str_replace( '-', '+', explode( '.', $response->access_token )[1] ) ) ) );

		/*
		 * Check to see if the user exists and do the SSO
		 */
		if ( email_exists( $user_info->email ) ) {
			$user = get_user_by( 'email', $user_info->email );
			wp_clear_auth_cookie();
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			wp_safe_redirect( site_url() );
			exit();
		} else {
			$user_id = username_exists( $user_info->email );
			if ( ! $user_id and email_exists( $user_info->email ) == false ) {
				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
				$user_id         = wp_create_user( $user_info->email, $random_password, $user_info->email );
				wp_clear_auth_cookie();
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );
				wp_safe_redirect( site_url() );
				exit;
			}
		}

	}
}