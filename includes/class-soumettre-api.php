<?php
/**
 * Class that call Soumettre API
 */

class Soumettre_Api {
	public $url;
	public $wp_api_url;
	public $soumettre_api_url;
	private $api_key;
	private $api_secret;

	public function __construct() {
		$this->url        = get_home_url();
		$this->wp_api_url = get_rest_url() . 'soumettre/v' . SOUMETTRE_REST_API_VERSION . '/';

		$this->soumettre_api_url = SOUMETTRE_DEBUG ? 'http://soumettre.test/api/remote/' : 'https://soumettre.fr/api/remote/';
	}

	/**
	 * Api key setter
	 *
	 * @param string $api_key
	 *
	 * @return void
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Api secret setter
	 *
	 * @param string $api_secret
	 *
	 * @return void
	 */
	public function set_api_secret( $api_secret ) {
		$this->api_secret = $api_secret;
	}

	/**
	 * Check if user exist and gateway is ok
	 *
	 * @return array|WP_Error
	 */
	public function check_api_auth() {
		$body = array(
			'site'          => $this->url,
			'site_api_path' => $this->wp_api_url,
		);

		$headers = array(
			'Content-Type' => 'application/json',
			'data_format'  => 'body',
		);

		return $this->makePostRequest( $this->soumettre_api_url . 'check_auth', $headers, $body );
	}

	/**
	 * Add current website to Soumettre spots
	 *
	 * @return array|WP_Error
	 */
	// public function add_website() {
	// $body = array(
	// 'site'          => $this->url,
	// 'site_api_path' => $this->wp_api_url,
	// );

	// $response = $this->makePostRequest( $this->soumettre_api_url . 'add_website', array(), $body );

	// if ( is_wp_error( $response ) ) {
	// return new WP_Error( wp_remote_retrieve_response_message( $response ) );
	// }

	// if ( 201 == wp_remote_retrieve_response_code( $response ) ) {
	// return json_decode( wp_remote_retrieve_body( $response ) );
	// }

	// return new WP_Error( 500, $this->get_error_message( $response ) );
	// }

	/**
	 * Remove current website from Soumettre spots
	 *
	 * @return
	 */
	public function remove_website() {
		$body = array(
			'site' => $this->url,
		);

		$response = $this->makePostRequest( $this->soumettre_api_url . 'remove_website', array(), $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return wp_remote_retrieve_response_message( $response );
	}

	protected function get_error_message( $response ) {
		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body ) {
			return __( 'unknown error', 'soumettrefr' );
		}

		$json = json_decode( $body );

		if ( ! isset( $json->message ) ) {
			return __( 'unknown error', 'soumettrefr' );
		}

		return $json->message;
	}

	/**
	 * Make Post request
	 *
	 * @param string $url URL to call
	 * @param array  $headers
	 *
	 * @return array|WP_Error
	 */
	private function makePostRequest( $url, $headers = array(), $body = array() ) {
		$body['time']      = time();
		$body['api_key']   = $this->api_key;
		$body['signature'] = $this->make_signature( $body['time'] );
		$body['version']   = SOUMETTRE_VERSION;
		$body['lang']      = get_user_locale();
		$body['cms']       = 'WordPress';

		$headers['Content-Type'] = 'application/json';
		$headers['data_format']  = 'body';

		return wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $body ),
				'headers' => $headers,
			)
		);
	}

	/**
	 * Make Soumettre signature
	 */
	private function make_signature( $time ) {
		return md5( "{$this->api_key}-{$this->api_secret}-$time" );
	}
}
