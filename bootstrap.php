<?php
/**
 * The DWS WordPress Framework bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\wordpress-framework-core
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
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Since this file is autoloaded by Composer, 'exit' breaks all external dev tools.
}

use DeepWebSolutions\Framework\Core\v1_0_0 as CoreFramework;

// Maybe stop loading if this version of the framework has already been loaded by another component.
if ( defined( 'DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR' ) ) {
	// This version of the DWS Core Framework has already been loaded by another plugin. No point in reloading...
	return;
}
define( 'DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR', 'v1.0.0' ); // The suffix must be unique across all versions of the core.

if ( ! function_exists( 'dws_wp_framework_constant_get_versioned_name' ) ) {
	/**
	 * Create a versioned variant of a constant's name.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $constant_name  The name of the constant.
	 * @param   string  $version        The version qualifier that should be added to the constant.
	 *
	 * @return  string
	 */
	function dws_wp_framework_constant_get_versioned_name( string $constant_name, string $version ) {
		return join( '_', array( $constant_name, md5( $version ) ) );
	}
}

define( dws_wp_framework_constant_get_versioned_name( 'DWS_WP_FRAMEWORK_CORE_MIN_PHP', DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR ), '7.4' );
define( dws_wp_framework_constant_get_versioned_name( 'DWS_WP_FRAMEWORK_CORE_MIN_WP', DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR ), '5.4' );

// The following settings can be overwritten in a configuration file.
defined( 'DWS_WP_FRAMEWORK_WHITELABEL_NAME' ) || define( 'DWS_WP_FRAMEWORK_WHITELABEL_NAME', 'Deep Web Solutions' );
defined( 'DWS_WP_FRAMEWORK_WHITELABEL_LOGO' ) || define( 'DWS_WP_FRAMEWORK_WHITELABEL_LOGO', __DIR__ . '/src/assets/dws_logo.svg' );

defined( 'DWS_WP_FRAMEWORK_CORE_NAME' ) || define( 'DWS_WP_FRAMEWORK_CORE_NAME', DWS_WP_FRAMEWORK_WHITELABEL_NAME . ': Framework Core' );
defined( 'DWS_WP_FRAMEWORK_TEMP_DIR_NAME' ) || define( 'DWS_WP_FRAMEWORK_TEMP_DIR_NAME', 'deep-web-solutions' );

defined( 'DWS_WP_FRAMEWORK_TEMP_DIR_PATH' ) || define( 'DWS_WP_FRAMEWORK_TEMP_DIR_PATH', wp_get_upload_dir() ['basedir'] . DIRECTORY_SEPARATOR . DWS_WP_FRAMEWORK_TEMP_DIR_NAME . DIRECTORY_SEPARATOR );
defined( 'DWS_WP_FRAMEWORK_TEMP_DIR_URL' ) || define( 'DWS_WP_FRAMEWORK_TEMP_DIR_URL', wp_get_upload_dir() ['baseurl'] . '/' . DWS_WP_FRAMEWORK_TEMP_DIR_NAME . '/' );

// Define a few general-use functions for requirement checking.
if ( ! function_exists( 'dws_wp_framework_check_php_wp_requirements_met' ) ) {
	/**
	 * Checks if the system requirements are met.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $min_php_version    The minimum PHP version required to run.
	 * @param   string  $min_wp_version     The minimum WP version required to run.
	 *
	 * @return  bool
	 */
	function dws_wp_framework_check_php_wp_requirements_met( string $min_php_version, string $min_wp_version ) {
		if ( version_compare( PHP_VERSION, $min_php_version, '<' ) ) {
			return false;
		} elseif ( version_compare( $GLOBALS['wp_version'], $min_wp_version, '<' ) ) {
			return false;
		}

		return true;
	}
}
if ( ! function_exists( 'dws_wp_framework_output_requirements_error' ) ) {
	/**
	 * Prints an error that the system requirements weren't met.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $component_name     The name of the component that wants to record the error.
	 * @param   string  $min_php_version    The minimum PHP version required to run.
	 * @param   string  $min_wp_version     The minimum WP version required to run.
	 * @param   array   $args               Associative array of other variables that should be made available in the template's context.
	 */
	function dws_wp_framework_output_requirements_error( string $component_name, string $min_php_version, string $min_wp_version, array $args = array() ) {
		add_action(
			'admin_notices',
			function() use ( $component_name, $min_php_version, $min_wp_version, $args ) {
				require_once __DIR__ . '/src/templates/requirements-error.php';
			}
		);
	}
}
if ( ! function_exists( 'dws_wp_framework_output_child_requirements_error' ) ) {
	/**
	 * Children plugins can use this to output an error when they fail to initialize because the framework's requirements weren't met.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $child_plugin_name      The name of the child plugin that failed to initialize because of a framework error.
	 * @param   array   $args                   Associative array of other variables that should be made available in the template's context.
	 */
	function dws_wp_framework_output_child_requirements_error( string $child_plugin_name, array $args = array() ) {
		add_action(
			'admin_notices',
			function() use ( $child_plugin_name, $args ) {
				require_once __DIR__ . '/src/templates/child-requirements-error.php';
			}
		);
	}
}

// Perform environment and installation checks and auto-load the framework.
$dws_framework_core_min_php_version_v1_0_0 = dws_wp_framework_constant_get_versioned_name( 'DWS_WP_FRAMEWORK_CORE_MIN_PHP', DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR );
$dws_framework_core_min_wp_version_v1_0_0  = dws_wp_framework_constant_get_versioned_name( 'DWS_WP_FRAMEWORK_CORE_MIN_WP', DWS_WP_FRAMEWORK_CORE_VERSION_HG847H8GFDHGIHERGR );
if ( ! dws_wp_framework_check_php_wp_requirements_met( $dws_framework_core_min_php_version_v1_0_0, $dws_framework_core_min_wp_version_v1_0_0 ) ) {
	dws_wp_framework_output_requirements_error( DWS_WP_FRAMEWORK_CORE_NAME, $dws_framework_core_min_php_version_v1_0_0, $dws_framework_core_min_wp_version_v1_0_0 );
	/* @noinspection PhpUnhandledExceptionInspection */
	throw new CoreFramework\Exceptions\MinimumRequirements( DWS_WP_FRAMEWORK_CORE_NAME );
}
