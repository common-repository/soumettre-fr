<?php
/**
 *
 * File:    class-soumettre-rest-controller.php
 * Date:    16/05/2020
 */

class Soumettre_Rest_Controller {
	public $soumettrePost;
	public $soumettreCategory;

	public function __construct() {
		$this->soumettrePost     = new SoumettrePost();
		$this->soumettreCategory = new SoumettreCategory();
	}

	public function get_version() {
		return rest_ensure_response( array(
			'version' => SOUMETTRE_VERSION,
			'status'  => 'ok',
		) );
	}

	public function get_categories() {
		$categories = $this->soumettreCategory->get_all();

		array_walk( $categories, function ( &$item ) {
			$item      = $this->filter_cat_fields( $item );
			$item->url = get_term_link( $item->id );
		} );

		return rest_ensure_response( $categories );
	}

	public function get_soumettre_contents() {
		$posts = $this->soumettrePost->get_soumettre_posts();

		if ( is_wp_error( $posts ) ) {
			return rest_ensure_response( $posts );
		}

		array_walk( $posts, function ( &$item ) {
			$item = $this->filter_post_fields( $item );
		} );

		return rest_ensure_response( $posts );
	}

	public function create_post( WP_REST_Request $request ) {
		$created = $this->soumettrePost->create(
			$request->get_param( 'title' ),
			$request->get_param( 'content' ),
			$request->get_param( 'soumettre_id' ),
			$request->get_param( 'category_id' ),
			$request->get_param( 'image' ),
			$request->get_param('title')
		);

		if ( is_wp_error( $created ) ) {
			return rest_ensure_response( $created );
		}

		$filtered_post = $this->filter_post_fields( $created );

		return new WP_REST_Response( $filtered_post, 201 );
	}

	public function show_post( WP_REST_Request $request ) {
		$post = $this->soumettrePost->get_by_id( $request->get_param( 'soumettre_id' ) );

		if ( is_wp_error( $post ) ) {
			return rest_ensure_response( $post );
		}

		$filtered_post = $this->filter_post_fields( $post );

		return rest_ensure_response( $filtered_post );
	}

	public function edit_post( WP_REST_Request $request ) {
		$post = $this->soumettrePost->edit( $request->get_param( 'title' ), $request->get_param( 'content' ), $request->get_param( 'soumettre_id' ) );

		if ( is_wp_error( $post ) ) {
			return rest_ensure_response( $post );
		}

		$filtered_post = $this->filter_post_fields( $post );

		return rest_ensure_response( $filtered_post );
	}

	public function delete_post( WP_REST_Request $request ) {
		$post = $this->soumettrePost->get_by_id( $request->get_param( 'soumettre_id' ) );

		if ( ! wp_delete_post( $post->ID ) ) {
			return rest_ensure_response( new WP_Error( 'delete-post', esc_html__( 'Unable to delete Soumettre.fr post', 'soumettrefr' ), array( 'status' => 400 ) ) );
		}

		return rest_ensure_response( [ 'status' => 'ok' ] );
	}

	public function set_commission( WP_REST_Request $request ) {
		update_option( 'soumettre_commission', $request->get_param( 'commissions' ) );
		update_option( 'soumettre_email', $request->get_param( 'user_email' ) );

		return rest_ensure_response( [ 'status' => 'ok' ] );
	}

	public function set_tokens( WP_REST_Request $request ) {
		update_option( 'soumettre_api_key', $request->get_param( 'api_key' ) );
		update_option( 'soumettre_api_secret', $request->get_param( 'api_secret' ) );
		update_option( 'soumettre_spot_id', $request->get_param( 'source_id' ) );
		update_option( 'soumettre_email', $request->get_param( 'user_email' ) );

		if ( ! wp_next_scheduled( 'soumettre_cron_hook' ) ) {
			$nextWeek = new DateTime( '+1 week' );
			wp_schedule_event( $nextWeek->getTimestamp(), 'soumettre_check_weekly', 'soumettre_cron_hook' );
		}

		return rest_ensure_response( [ 'status' => 'ok' ] );
	}

	protected function filter_cat_fields( $category ) {
		/**
		 * Champs à retourner à Soumettre
		 */
		$fields_to_return = array( 'id', 'text', 'count', 'parent' );
		$category->id     = $category->term_id;
		$category->text   = $category->name;

		$newObj = new stdClass();
		foreach ( $fields_to_return as $field ) {
			$newObj->$field = $category->$field;
		}

		return $newObj;
	}

	protected function filter_post_fields( WP_Post $post ) {
		/**
		 * Champs à retourner à Soumettre
		 */
		$fields_to_return = array(
			'ID',
			'post_date',
			'post_content',
			'post_title',
			'post_status',
			'post_name',
		);

		$newObj = new stdClass();
		foreach ( $fields_to_return as $field ) {
			$newObj->$field = $post->$field;
		}
		$newObj->url    = get_permalink( $newObj->ID );
		$newObj->status = 'ok';

		return $newObj;
	}
}
