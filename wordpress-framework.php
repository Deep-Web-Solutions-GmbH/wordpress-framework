<?php
/**
 * The DWS WordPress Framework bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\wordpress-framework
 * @author              Deep Web Solutions GmbH
 * @copyright           2020 Deep Web Solutions GmbH
 * @license             GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         DWS WordPress Framework
 * Description:         A set of related classes and helpers to kick start WordPress plugin development.
 * Version:             1.0.0
 * Author:              Deep Web Solutions GmbH
 * Author URI:          https://www.deep-web-solutions.de
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         dws-wp-framework
 * Domain Path:         /src/languages
 */

namespace DeepWebSolutions\Framework;

defined( 'ABSPATH' ) || exit;

define( 'DWS_WP_FRAMEWORK_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DWS_WP_FRAMEWORK_BASE_URL', plugin_dir_url( __FILE__ ) );

defined( 'DWS_WP_FRAMEWORK_WHITELABEL_NAME' ) || define( 'DWS_WP_FRAMEWORK_WHITELABEL_NAME', 'Deep Web Solutions' );
defined( 'DWS_WP_FRAMEWORK_WHITELABEL_LOGO' ) || define( 'DWS_WP_FRAMEWORK_WHITELABEL_LOGO', DWS_WP_FRAMEWORK_BASE_PATH . 'src/admin/assets/dws_logo.svg' );

define( 'DWS_WP_FRAMEWORK_NAME', DWS_WP_FRAMEWORK_WHITELABEL_NAME . ': Framework' );
define( 'DWS_WP_FRAMEWORK_MIN_PHP', '7.4' );
define( 'DWS_WP_FRAMEWORK_MIN_WP', '5.4' );

defined( 'DWS_WP_FRAMEWORK_TEMP_DIR' ) || define( 'DWS_WP_FRAMEWORK_TEMP_DIR', trailingslashit( trailingslashit( wp_upload_dir( null, false ) ['basedir'] ) . 'deep-web-solutions') );

/**
 * Checks if the system requirements are met.
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool
 */
function dws_wp_framework_requirements_met() {
	if ( version_compare( PHP_VERSION, DWS_WP_FRAMEWORK_MIN_PHP, '<' ) ) {
		return false;
	} elseif ( version_compare( $GLOBALS['wp_version'], DWS_WP_FRAMEWORK_MIN_WP, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wp_framework_requirements_error() {
	/* @noinspection PhpIncludeInspection */
	require_once DWS_WP_FRAMEWORK_BASE_PATH . 'src/admin/templates/requirements-error.php';
}

/**
 * Prints an error that the installation probably failed.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_wp_framework_installation_error() {
	/* @noinspection PhpIncludeInspection */
	require_once DWS_WP_FRAMEWORK_BASE_PATH . 'src/admin/templates/installation-error.php';
}

if ( dws_wp_framework_requirements_met() ) {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	} else {
		add_action( 'admin_notices', 'dws_wp_framework_installation_error' );
	}
} else {
	add_action( 'admin_notices', 'dws_wp_framework_requirements_error' );
}