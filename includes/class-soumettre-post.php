<?php
/**
 * Manage Soumettre Posts
 */

// Importer les fonctions d’admin
if ( ! function_exists( 'media_handle_upload' ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
}

/**
 * Class SoumettrePost
 */
class SoumettrePost {

	public function get_soumettre_posts() {
		$args_posts = array(
			'numberposts' => - 1,
			'post_status' => array( 'any' ),
			'meta_query'  => array(
				'relation' => 'OR',
				array(
					'key'     => 'soumettre_id',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_soumettre_task_id',
					'compare' => 'EXISTS',
				),
			),
		);

		$args_posts = apply_filters( 'soumettre_get_posts_params', $args_posts );

		return get_posts( $args_posts );
	}

	/**
	 * @param $title
	 * @param $content
	 * @param $soumettre_id
	 * @param $category_id
	 * @param $featured_image_url
	 * @param null $featured_image_title
	 *
	 * @return array|int|WP_Error|WP_Post|null
	 */
	public function create( $title, $content, $soumettre_id, $category_id, $featured_image_url, $featured_image_title = null ) {
		// Get default author
		$author_id = get_option( 'soumettre_default_author', 0 );

		$args_random_author = apply_filters(
			'soumettre_author_random_picked_args',
			array(
				'role_in' => array( 'administrator', 'author', 'editor' ),
				'fields'  => 'ID',
			)
		);

		if ( ! $author_id ) {
			$users = get_users( $args_random_author );

			if ( ! empty( $users ) ) {
				$random_user_key = array_rand( $users );
				$author_id       = $users[ $random_user_key ];
			}

			// Aucun utilisateur n'a pu être trouvé
			// TODO gestion de l'alerte
		}

		// On créé d'abord le post pour avoir un ID
		$post_args = array(
			'post_author' => $author_id,
			'post_title'  => sanitize_text_field( $title ),
			'post_name'   => sanitize_title( $title ),
			'post_status' => 'draft',
			'meta_input'  => array(
				'soumettre_id' => $soumettre_id,
			),
		);

		// If post already exists, we must update instead of create
		$post_exists = $this->get_by_id( $soumettre_id );
		if ( ! is_wp_error( $post_exists ) ) {
			$post_args['ID'] = $post_exists->ID;
		}

		$inserted_post = wp_insert_post( $post_args, true );

		if ( is_wp_error( $inserted_post ) ) {
			return $inserted_post;
		}

		$content = $this->parse_content( $content, $inserted_post );

		$values_to_update = array(
			'ID'           => $inserted_post,
			'post_content' => wp_check_invalid_utf8( $content, true ),
			'post_status'  => 'publish',
		);

		wp_update_post( $values_to_update, true );

		wp_set_post_categories( $inserted_post, $category_id );

		// Set featured image
		if ( $featured_image_url !== '' ) {
			if ( ! $id_attachment = $this->get_attachment_by_post_meta( $featured_image_url ) ) {
				$id_attachment = $this->import_img( $featured_image_url, $inserted_post, $featured_image_title );
			}
			if ( ! is_wp_error( $id_attachment ) ) {
				$featured_image_alt = ($featured_image_title) ? $featured_image_title : $title;
				update_post_meta($id_attachment, '_wp_attachment_image_alt', apply_filters('soumettre_default_alt_featued_image', $featured_image_alt, $category_id));
				set_post_thumbnail( $inserted_post, $id_attachment );
			}
		}

		return get_post( $inserted_post );
	}

	public function get_by_id( $soumettre_id ) {
		$post = get_posts(
			array(
				'numberposts' => 1,
				'post_status' => array( 'any' ),
				'meta_query'  => array(
					'relation' => 'OR',
					array(
						'key'     => 'soumettre_id',
						'value'   => $soumettre_id,
						'compare' => '=',
					),
					array(
						'key'     => '_soumettre_task_id',
						'value'   => $soumettre_id,
						'compare' => '=',
					),
				),
			)
		);

		if ( empty( $post ) ) {
			return new WP_Error( '404', __( 'Missing post', 'soumettrefr' ) );
		}

		return $post[0];
	}

	public function edit( $title, $content, $soumettre_id ) {
		$post = $this->get_by_id( $soumettre_id );

		if ( ! $post ) {
			return new WP_Error( 404, 'Missing post' );
		}

		$values_to_update = array(
			'ID'           => $post->ID,
			'post_content' => wp_check_invalid_utf8( $this->parse_content( $content, $post->ID ), true ),
			'post_title'   => sanitize_text_field( $title ),
		);

		$updated_posts = wp_update_post( $values_to_update, true );

		if ( is_wp_error( $updated_posts ) ) {
			return $updated_posts;
		}

		return get_post( $updated_posts );
	}

	protected function parse_content( $content, $post_id ) {
		/** @var simple_html_dom $html */
		$html = str_get_html( $content );
		foreach ( $html->find( 'img' ) as &$img ) {
			$attachment_id = $this->get_attachment_by_post_meta( $img->src );

			if ( ! $attachment_id ) {
				$attachment_id = $this->import_img( $img->src, $post_id );
			}
			/**
			 * Taille de l'image à insérer dans l'article. Peut être une taille standard ou un tableau de pixel. Largeur puis hauteur.
			 */
			$image_content_size = apply_filters( 'soumettre_img_content_size', array( 800, 800 ) );
			$attachment_url     = wp_get_attachment_image_url( $attachment_id, $image_content_size );
			$img->src           = $attachment_url;

			if ( isset( $img->width ) ) {
				$img->width = null;
			}

			if ( isset( $img->height ) ) {
				$img->height = null;
			}
		}

		// Sanitize HTML
		return $html->save();
	}

	/**
	 * Retourne un media déjà importé
	 *
	 * @param $url
	 *
	 * @return bool|mixed
	 */
	protected function get_attachment_by_post_meta( $url ) {
		$args      = array(
			'post_per_page' => 1,
			'post_type'     => 'attachment',
			'post_status'   => 'inherit',
			'meta_query'    => array(
				array(
					'key'   => 'soumettre_url',
					'value' => trim( $url ),
				),
			),
		);
		$get_posts = new WP_Query( $args );

		if ( isset( $get_posts->posts[0] ) ) {
			return $get_posts->posts[0]->ID;
		}

		return false;
	}

	/**
	 * Import d'image dans WordPress
	 *
	 * @param $url
	 * @param $post_id
	 * @param null $title
	 * @param null $alt
	 *
	 * @return int|WP_Error
	 *
	 * @author Willy Bahuaud
	 * @see https://wabeo.fr/migration-donnees-wordpress/
	 */
	protected function import_img( $url, $post_id, $title = null ) {
		$tmp        = download_url( $url );
		$file_array = array();

		$img_exists = preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png|pdf)/i', $url, $matches );

		if ( ! $img_exists ) {
			return;
		}

		$file_array['name']     = basename( $matches[0] );

		if ( $title ) {
			$file_array['name'] = sanitize_title( $title ) . '.' . $matches[1];
		}


		$file_array['tmp_name'] = $tmp;

		if ( is_wp_error( $tmp ) ) {
		    return $tmp;
		}

		$id = media_handle_sideload( $file_array, $post_id );

		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return $id;
		}
		update_post_meta( $id, 'soumettre_url', $url );

		return $id;
	}

}
