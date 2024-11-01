<?php

/**
 * Fired during plugin activation
 *
 * @link       https://soumettre.fr/
 * @since      1.0.0
 *
 * @package    Soumettre
 * @subpackage Soumettre/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Soumettre
 * @subpackage Soumettre/includes
 * @author     Soumettre <technique@soumettre.fr>
 */
class Soumettre_Activator {

	public static function activate() {

		// Old installation ?
		$old_api_key    = get_option( 'soum_sour_api_key' );
		$old_api_secret = get_option( 'soum_sour_api_secret' );
		$old_author     = get_option( 'soum_sour_author' );

		if ( $old_api_key && $old_api_secret && $old_author ) {
			update_option( 'soumettre_api_key', $old_api_key );
			update_option( 'soumettre_api_secret', $old_api_secret );
			update_option( 'soumettre_default_author', $old_author );

			delete_option( 'soum_sour_api_key' );
			delete_option( 'soum_sour_api_secret' );
			delete_option( 'soum_sour_author' );
			delete_option( 'soum_sour_email' );

			set_transient( 'soumettre_plugin_old_plugin_version', true, 60 );
		}

		// Uninstall old plugin
        $plugins_list = get_plugins();

		foreach($plugins_list as $plugin_file => $datas) {
		    if (0 === strpos($plugin_file, 'soumettre-partner')) {
			    deactivate_plugins(array($plugin_file));
			    delete_plugins(array($plugin_file));
            }
        }


		$current_api_key    = get_option( 'soumettre_api_key' );
		$current_api_secret = get_option( 'soumettre_api_secret' );

		if ( ! $current_api_key || ! $current_api_secret ) {
			return;
		}

		$api = new Soumettre_Api();
		$api->set_api_key( $current_api_key );
		$api->set_api_secret( $current_api_secret );

		$response = $api->check_api_auth();

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );

		if ( ! isset( $body->source_id ) ) {
			return;
		}

		update_option( 'soumettre_spot_id', $body->source_id );

		if ( ! wp_next_scheduled( 'soumettre_cron_hook' ) ) {
			$nextWeek = new DateTime( '+1 week' );
			wp_schedule_event( $nextWeek->getTimestamp(), 'soumettre_check_weekly', 'soumettre_cron_hook' );
		}
	}

	public function soumettre_notice_old_plugin() {
		?>
        <div class="notice notice-warning soumettre-notice">
			<?php esc_html_e( 'New Soumettre.fr plugin activated. You can remove your old plugin.', 'soumettrefr' ); ?>
        </div>
		<?php
	}

}
