<?php


/**
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    Soumettre
 * @subpackage Soumettre/includes
 * @author     Soumettre <technique@soumettre.fr>
 */
class Soumettre_Uninstall {

	public static function uninstall() {
		$deleted_options = array(
			'soumettre_settings',
			'soumettre_api_key',
			'soumettre_api_secret',
			'soumettre_show_admin_infos',
			'soumettre_default_author',
			'soumettre_commission',
			'soumettre_spot_id',
		);

		require_once plugin_dir_path( __FILE__ ) . 'class-soumettre-api.php';

		$current_api_key    = get_option( 'soumettre_api_key' );
		$current_api_secret = get_option( 'soumettre_api_secret' );

		$soumettreApi = new Soumettre_Api();
		$soumettreApi->set_api_key( $current_api_key );
		$soumettreApi->set_api_secret( $current_api_secret );
		$soumettreApi->remove_website();

		foreach ( $deleted_options as $option ) {
			delete_option( $option );
			delete_site_option( $option );
		}
	}

}
