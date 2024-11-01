<?php
/**
 * Manage validating callback methods
 */

class SoumettreValidateRestArgs {

	public function create_cat_validate_cat_name_arg( $value ) {
		if ( ! is_string( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'cat_name argument must be a string.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( term_exists( $value, 'category' ) ) {
			return new WP_Error( 'rest_duplicate_param', esc_html__( 'Category already exists.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function create_cat_validate_cat_slug_arg( $value ) {
		if ( ! is_string( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'cat_name argument must be a string.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( term_exists( $value, 'category' ) ) {
			return new WP_Error( 'rest_duplicate_param', esc_html__( 'Category already exists.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function create_cat_validate_cat_parent_arg( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'cat_parent must be a integer.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( ! term_exists( $value, 'category' ) && $value != 0 ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'cat_parent doesn\'t exists.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function create_content_validate_soumettre_id_arg( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'soumettre_id must be an integer.', 'soumettrefr' ), array( 'status' => 400 ) );
		}
	}

	public function create_content_validate_author_id_arg( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'author_id must be an integer.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( false === get_userdata( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'Author doesn\'t exists', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function create_content_validate_image_arg( $value ) {
		if ( ! is_string( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'image must be an URL.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( $value === "" ) {
			return true;
		}

		if ( false === SOUMETTRE_DEBUG && ! wp_http_validate_url( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'image must be a valid URL.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}


	public function create_content_validate_category_id_arg( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'category_id must be an integer.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		if ( ! term_exists( intval( $value ), '' ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'Category doesn\'t exists.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function soumettre_id_exists( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'category_id must be an integer.', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		$soumettrePost = new SoumettrePost();
		$result        = $soumettrePost->get_by_id( $value );

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'Post with this soumettre_id doesn\'t exsits', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function check_amount_commission_field( $value ) {
		$allowedCommissions = array(
			'soumettre',
			'catalog',
		);

		if ( ! is_array( $value ) ) {
			/* translators: %s: parameter type */
			$message = sprintf( esc_html_x( 'Commission amount should be a json array. You sent a %s', 'Display the type of var', 'soumettrefr' ), gettype( $value ) );

			return new WP_Error(
				'rest_invalid_param',
				$message,
				array( 'status' => 400 )
			);
		}

		foreach ( $value as $key => $item ) {
			if ( ! in_array( $key, $allowedCommissions ) ) {
				unset( $value[ $key ] );
			}
		}

		$value = array_filter( $value );

		if ( empty( $value ) ) {
			return new WP_Error( 'rest_invalid_param', esc_html__( 'Commission amount must be filled', 'soumettrefr' ), array( 'status' => 400 ) );
		}

		return true;
	}
}
