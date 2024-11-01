<?php

/**
 * Class Soumettre_Rest_Route
 *
 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#examples
 */
class Soumettre_Rest_Route extends WP_REST_Controller {

	protected $soumettre_rest_controller;
	protected $soumettre_rest_validate;
	protected $api_key;
	protected $api_secret;

	public function __construct() {
		$this->soumettre_rest_controller = new Soumettre_Rest_Controller();
		$this->soumettre_rest_validate   = new SoumettreValidateRestArgs();
		$this->api_key                   = get_option( 'soumettre_api_key', false );
		$this->api_secret                = get_option( 'soumettre_api_secret', false );
	}

	public function register_routes() {
		$namespace = 'soumettre/v' . SOUMETTRE_REST_API_VERSION;

		// Version
		register_rest_route( $namespace, '/version', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'get_version' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
			),
		) );

		register_rest_route( $namespace, '/tokens', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'set_tokens' ),
				'permission_callback' => array( $this, 'check_api_nonce' ),
				'args'                => array(
					'nonce'      => array(
						'description' => esc_html__( 'nonce to secure call', 'soumettrefr' ),
						'type'        => 'string',
						'requred'     => true,
					),
					'api_key'    => array(
						'description' => esc_html__( 'API key', 'soumettrefr' ),
						'type'        => 'string',
						'requred'     => true,
					),
					'api_secret' => array(
						'description' => esc_html__( 'API secret', 'soumettrefr' ),
						'type'        => 'string',
						'requred'     => true,
					),
					'user_email' => array(
						'description' => esc_html__( 'Soumettre.fr user email', 'soumettrefr' ),
						'type'        => 'string',
						'requred'     => false,
					),
				),
			),
		) );

		register_rest_route( $namespace, '/commission', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'set_commission' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
				'args'                => array(
					'commissions' => array(
						'description' => esc_html__( 'Amount of commissions', 'soumettrefr' ),
						'requred'     => true,
					),
					'user_email' => array(
						'description' => esc_html__( 'Soumettre.fr user email', 'soumettrefr' ),
						'type'        => 'string',
						'requred'     => false,
					),
				),
			),
		) );

		// Categories
		register_rest_route( $namespace, '/categories', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'get_categories' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
			),
		) );

		// Posts
		register_rest_route( $namespace, '/posts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'get_soumettre_contents' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'create_post' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
				'args'                => array(
					'title'        => array(
						'description' => esc_html__( 'Post title', 'soumettrefr' ),
						'type'        => 'string',
						'required'    => true,
					),
					'content'      => array(
						'description' => esc_html__( 'Post content', 'soumettrefr' ),
						'type'        => 'string',
						'required'    => true,
					),
					'soumettre_id' => array(
						'description'       => esc_html__( 'Soumettre.fr content ID', 'soumettrefr' ),
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => array(
							$this->soumettre_rest_validate,
							'create_content_validate_soumettre_id_arg'
						),
					),
					'category_id'  => array(
						'description'       => esc_html__( 'Post category ID', 'soumettrefr' ),
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => array(
							$this->soumettre_rest_validate,
							'create_content_validate_category_id_arg'
						),
					),
					'image'        => array(
						'description'       => esc_html__( 'Featured image URL', 'soumettrefr' ),
						'type'              => 'string',
						'validate_callback' => array(
							$this->soumettre_rest_validate,
							'create_content_validate_image_arg'
						),
						'default'           => '',
					),
				),
			),
		) );

		// Post
		register_rest_route( $namespace, '/posts/(?P<soumettre_id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'show_post' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
				'args'                => array(
					'soumettre_id' => array(
						'description'       => esc_html__( 'Soumettre.fr content ID', 'soumettrefr' ),
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => array( $this->soumettre_rest_validate, 'soumettre_id_exists' ),
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'edit_post' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
				'args'                => array(
					'title'        => array(
						'description' => esc_html__( 'Post title', 'soumettrefr' ),
						'type'        => 'string',
						'required'    => true,
					),
					'content'      => array(
						'description' => esc_html__( 'Post content', 'soumettrefr' ),
						'type'        => 'string',
						'required'    => true,
					),
					'soumettre_id' => array(
						'description'       => esc_html__( 'Soumettre.fr content ID', 'soumettrefr' ),
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => array( $this->soumettre_rest_validate, 'soumettre_id_exists' ),
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this->soumettre_rest_controller, 'delete_post' ),
				'permission_callback' => array( $this, 'check_api_permission' ),
				'args'                => array(
					'soumettre_id' => array(
						'description'       => esc_html__( 'Soumettre.fr content ID', 'soumettrefr' ),
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => array( $this->soumettre_rest_validate, 'soumettre_id_exists' ),
					),
				),
			),
		) );
	}

	public function check_api_permission( WP_REST_Request $request ) {
		return $this->check_signature( $request );
	}

	private function check_signature( WP_REST_Request $request ) {
		$received_signature = $request->get_param( 'signature' );
		$calculatedSign     = $this->make_signature( $request );

		return ( $received_signature === $calculatedSign );
	}

	private function make_signature( WP_REST_Request $request ) {
		return md5( "{$this->api_key}-{$this->api_secret}-{$request->get_param('time')}" );
	}

	public function check_api_nonce( WP_REST_Request $request ) {
		$base_nonce = get_transient( 'soumettre_nonce' );
		return ( $base_nonce === $request->get_param( 'nonce' ) );
	}
}
