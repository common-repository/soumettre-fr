<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://soumettre.fr/
 * @since             2.0.0
 * @package           Soumettre
 *
 * @wordpress-plugin
 * Plugin Name:       Soumettre.fr
 * Plugin URI:        https://soumettre.fr/devenir-partenaire
 * Description:       Connects your website to the Soumettre.fr platform
 * Version:           2.1.4
 * Author:            Bleetic
 * Author URI:        https://soumettre.fr/
 * Requires at least: 4.7
 * Requires PHP:      5.6
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       soumettrefr
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SOUMETTRE_VERSION', '2.1.4' );
define( 'SOUMETTRE_REST_API_VERSION', 2 );
define( 'SOUMETTRE_IMAGES', plugin_dir_url( __FILE__ ) . 'images/' );

if ( ! defined( 'SOUMETTRE_DEBUG' ) ) {
	define( 'SOUMETTRE_DEBUG', false );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-soumettre-activator.php
 */
function soumettre_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-activator.php';
	Soumettre_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-soumettre-deactivator.php
 */
function soumettre_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-deactivator.php';
	Soumettre_Deactivator::deactivate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-soumettre-deactivator.php
 */
function soumettre_uninstall() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-uninstall.php';
	Soumettre_Uninstall::uninstall();
}


register_activation_hook( __FILE__, 'soumettre_activate' );
register_deactivation_hook( __FILE__, 'soumettre_deactivate' );
register_uninstall_hook( __FILE__, 'soumettre_uninstall' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-soumettre.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-post.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-category.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-api.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-soumettre-check-auth.php';
require plugin_dir_path( __FILE__ ) . 'public/rest/class-soumettre-rest-route.php';
require plugin_dir_path( __FILE__ ) . 'public/rest/class-soumettre-rest-controller.php';
require plugin_dir_path( __FILE__ ) . 'public/rest/class-soumettre-validate-rest-args.php';

if ( ! function_exists( 'file_get_html' ) ) {
	require plugin_dir_path( __FILE__ ) . 'lib/simple_html_dom.php';
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function soumettre_run() {
	$plugin = new Soumettre();
	$plugin->run();
}

soumettre_run();
