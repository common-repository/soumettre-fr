<?php
/**
 * Manage Categories
 */

/**
 * Class SoumettreCategory
 */
class SoumettreCategory {
	public function __construct() {
	}

	public function get_all() {
		/**
		 * Paramètres appliqués pour récupérer la liste des catégories
		 */
		$params = array( 'hide_empty' => false );
		$params = apply_filters( 'soumettre_get_categories_params', $params );

		return get_categories( $params );
	}

	public function get_by_id( $id ) {
		if ( ! is_int( $id ) ) {
			return new WP_Error( 'invalid id parameter', esc_html__( 'get_by_id method accept only int param.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return get_category( $id );
	}
}
