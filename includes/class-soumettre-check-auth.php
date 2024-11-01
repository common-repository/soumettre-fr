<?php


class Soumettre_Check_Auth {

	public $state;

	private $option_state_name = 'soumettre_auth_state';

	public function __construct() {
		$this->state = get_option( $this->option_state_name );
	}

	public function check_api_auth() {
		$api_key    = get_option( 'soumettre_api_key' );
		$api_secret = get_option( 'soumettre_api_secret' );

		if ( false === $api_secret || false === $api_key ) {
			return;
		}

		$api = new Soumettre_Api();
		$api->set_api_key( $api_key );
		$api->set_api_secret( $api_secret );
		$check_auth = $api->check_api_auth();

		$this->set_state( $check_auth );
	}

	protected function set_state( $check_auth ) {
		$state = $this->state;

		if ( false === $state ) {
			$state = $this->default_state();
		}

		$state->checked_at = time();

		$response_code = wp_remote_retrieve_response_code( $check_auth );

		if ( 200 !== $response_code ) {
			$state->status        = 'ko';
			$state->count_errors += 1;
		} else {
			$state->status       = 'ok';
			$state->count_errors = 0;
		}

		$this->state = $state;
		update_option( $this->option_state_name, $this->state );
	}

	protected function default_state() {
		$state               = new stdClass();
		$state->checked_at   = null;
		$state->status       = null;
		$state->count_errors = 0;

		return $state;
	}

}
