<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://soumettre.fr/
 * @since      1.0.0
 *
 * @package    Soumettre
 * @subpackage Soumettre/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Soumettre
 * @subpackage Soumettre/admin
 * @author     Soumettre <technique@soumettre.fr>
 */
class Soumettre_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Soumettre_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Soumettre_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/soumettre-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_settings_page() {

		/**
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		add_menu_page(
            _x( 'Soumettre.fr', 'Admin menu', 'soumettrefr' ),
            _x( 'Soumettre.fr', 'Admin menu', 'soumettrefr' ),
            'manage_options',
            'soumettre-options', array($this, 'display_plugin_options_page'),
			SOUMETTRE_IMAGES . 'soumettre-icon.png'
        );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_options_page() {
		include_once( 'partials/soumettre-admin-display.php' );
	}


	/**
	 * TODO: Refactor this method with Soumettre_Activator@activate
	 */
	public function check_api_auth() {
		$api_key    = get_option( 'soumettre_api_key', '' );
		$api_secret = get_option( 'soumettre_api_secret', '' );

		if ( '' === $api_key || '' === $api_secret ) {
			add_settings_error( 'soumettre_settings', 200, __( "Don't forget to connect your website.", 'soumettrefr' ), 'warning' );

			return;
		}

		$soumettreApi = new Soumettre_Api();
		$soumettreApi->set_api_key( $api_key );
		$soumettreApi->set_api_secret( $api_secret );

		$response = $soumettreApi->check_api_auth();

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			add_settings_error( 'soumettre_settings', 200, __( "Your API access is invalid. Check on <a href='https://soumettre.fr/user/api-token' target='_blank' rel='noopener'>Soumettre website</a> your API key and API secret.", 'soumettrefr' ), 'warning' );
		}
	}

	/**
	 * Settings fields
	 */
	public function register_settings() {
		register_setting( $this->plugin_name, 'soumettre_settings', array( $this, 'soumettre_settings_validation' ) );

		add_settings_section( 'soumettre_settings_options_section', __( 'Options', 'soumettrefr' ), array(
			$this,
			'api_settings_options_section_callback'
		), $this->plugin_name );

		add_settings_field( 'soumettre_field_default_author', __( 'Default Soumettre posts author', 'soumettrefr' ), array(
			$this,
			'soumettre_field_default_author_cb'
		), $this->plugin_name, 'soumettre_settings_options_section', array( 'label_for' => 'soumettre_default_author' ) );
	}

	public function api_settings_options_section_callback() {
	}

	public function soumettre_settings_validation( $datas ) {
		$default_author_id = intval( $datas['default_author'] );

		update_option( 'soumettre_default_author', $default_author_id );

		return $datas;
	}

	public function soumettre_settings_options_validation( $datas ) {
	}

	public function soumettre_field_default_author_cb() {
		$default_author_id = get_option( 'soumettre_default_author', false );
		$users             = get_users( array(
			'role__not_in' => array( 'subscriber' ),
			'fields'       => array( 'ID', 'user_login' ),
		) );
		?>
        <fieldset>
            <legend class="screen-reader-text"><?php _e( 'Default Soumettre posts author', 'soumettrefr' ); ?></legend>
			<?php
			wp_dropdown_users( array(
				'name'             => 'soumettre_settings[default_author]',
				'include_selected' => true,
				'selected'         => $default_author_id,
				'show_option_all'  => __( 'Random user', 'soumettrefr' ),
				'role__not_in'     => array( 'subscriber', 'contributor' ),
			) );
			?>
            <p class="description"><?php _e( 'Random user are picked in administrator, author and editor roles', 'soumettrefr' ); ?></p>
        </fieldset>
		<?php
	}

	public function soumettre_add_plugin_page_settings_link( $links ) {
		array_unshift( $links, '<a href="' . admin_url( 'options-general.php?page=soumettre-options' ) . '">' . __( 'Settings', 'soumettrefr' ) . '</a>' );

		return $links;
	}

	/**
	 * Define weekly schedule for Soumettre. (weekly event only available since WordPress 5.4
	 *
	 * @param $schedules array
	 *
	 * @return array
	 */
	public function soumettre_define_cron_interval( $schedules ) {
		$schedules['soumettre_check_weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Weekly', 'soumettrefr' ),
		);

		return $schedules;
	}

	public function soumettre_cron_check_api() {
		$check_auth = new Soumettre_Check_Auth();
		$check_auth->check_api_auth();

		$should_notice = get_option( 'soumettre_error_notice', true );

		if ( $check_auth->state->count_errors > 1 && $should_notice && 'stop' !== $should_notice ) {
			update_option( 'soumettre_error_notice', true );
		}
	}

	public function soumettre_admin_notice__error() {
		$should_notice = get_option( 'soumettre_error_notice' );
		if ( false === $should_notice || 'stop' === $should_notice ) {
			return;
		}

		?>
        <div class="notice notice-warning soumettre-notice">
            <p><?php esc_html_e( "Your website can not connect with Soumettre API. Check your API credentials. If it still don't work create a support ticket.", 'soumettrefr' ); ?></p>
            <p><a href="?soumettre-dismiss-notice"><?php esc_html_e( 'Close', 'soumettrefr' ); ?></a> | <a
                        href="?soumettre-disable-notice"><?php esc_html_e( "Do not display anymore.", 'soumettrefr' ); ?></a>
            </p>
        </div>
		<?php
	}

	public function soumettre_can_delete_old_plugin() {
		if ( ! get_transient( 'soumettre_plugin_old_plugin_version' ) ) {
			return;
		}

		delete_transient( 'soumettre_plugin_old_plugin_version' );
		?>
        <div class="notice notice-info soumettre-notice">
            <p><?php esc_html_e( "Old Soumettre.fr plugin seen, your settings have been recovered and plugin deleted.", 'soumettrefr' ); ?></p>
        </div>

		<?php
	}

	public function soumettre_dismiss_notice() {
		if ( isset( $_GET['soumettre-dismiss-notice'] ) ) {
			delete_option( 'soumettre_error_notice' );
		}

		if ( isset( $_GET['soumettre-disable-notice'] ) ) {
			update_option( 'soumettre_error_notice', 'stop' );
		}
	}

	public function soumettre_display_notice() {
		if ( ! isset( $_GET['soumettre_notice_status'] ) || ! isset( $_GET['soumettre_notice_message'] ) ) {
			return;
		}


		?>
        <div class="notice is-dismissible notice-<?php echo esc_attr( $_GET['soumettre_notice_status'] ); ?> soumettre-notice">
            <p><?php echo esc_html( $_GET['soumettre_notice_message'] ); ?></p>
        </div>
		<?php
	}

	public function soumettre_disconnect_gateway() {
        if (current_user_can('manage_options')) {
            $nextCronTimestamp = wp_next_scheduled('soumettre_cron_hook');

            if ($nextCronTimestamp) {
                wp_unschedule_event($nextCronTimestamp, 'soumettre_cron_hook');
            }

            delete_option('soumettre_auth_state');

            delete_option('soumettre_api_key');
            delete_option('soumettre_api_secret');

            exit(wp_redirect(admin_url('options-general.php?page=soumettre-options')));
        }

        exit(wp_redirect(home_url()));
	}
}
