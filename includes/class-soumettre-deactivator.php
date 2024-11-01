<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Soumettre
 * @subpackage Soumettre/includes
 * @author     Soumettre <technique@soumettre.fr>
 */
class Soumettre_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$nextCronTimestamp = wp_next_scheduled( 'soumettre_cron_hook' );

		if ( $nextCronTimestamp ) {
			wp_unschedule_event( $nextCronTimestamp, 'soumettre_cron_hook' );
		}

		delete_option( 'soumettre_auth_state' );

		$current_api_key    = get_option( 'soumettre_api_key' );
		$current_api_secret = get_option( 'soumettre_api_secret' );

		if ( $current_api_key && $current_api_secret ) {
			$soumettreApi = new Soumettre_Api();
			$soumettreApi->set_api_key( $current_api_key );
			$soumettreApi->set_api_secret( $current_api_secret );
			$soumettreApi->remove_website();
		}
	}

}
