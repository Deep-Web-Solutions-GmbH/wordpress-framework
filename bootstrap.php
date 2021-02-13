<?php
/**
 * The DWS WordPress Framework Core bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\WP-Framework\Core
 * @author              Deep Web Solutions GmbH
 * @copyright           2020 Deep Web Solutions GmbH
 * @license             GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         DWS WordPress Framework Core
 * Description:         A set of related classes to kick start WordPress development.
 * Version:             1.0.0
 * Requires at least:   5.5
 * Requires PHP:        7.4
 * Author:              Deep Web Solutions GmbH
 * Author URI:          https://www.deep-web-solutions.com
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         dws-wp-framework-core
 * Domain Path:         /src/languages
 */

namespace DeepWebSolutions\Framework;

use DeepWebSolutions\Framework\Core\Abstracts\PluginBase;
use DeepWebSolutions\Framework\Core\Exceptions\Initialization\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;
use DeepWebSolutions\Framework\Core\Exceptions\Initialization\PluginInitializationFailure;

if ( ! defined( 'ABSPATH' ) ) {
	return; // Since this file is autoloaded by Composer, 'exit' breaks all external dev tools.
}

// Start by autoloading dependencies and defining a few functions for running the bootstrapper.
// The conditional check makes the whole thing compatible with Composer-based WP management.
file_exists( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';

// Define core constants.
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_NAME', dws_wp_framework_get_whitelabel_name() . ': Framework Core' );
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_VERSION', '1.0.0' );

/**
 * Returns the whitelabel name of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_name(): string {
	return constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_NAME' );
}

/**
 * Returns the version of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_version(): string {
	return constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_VERSION' );
}

// Define minimum environment requirements.
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_PHP', '7.4' );
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_WP', '5.5' );

/**
 * Returns the minimum PHP version required to run the Bootstrapper of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_min_php(): string {
	return constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_PHP' );
}

/**
 * Returns the minimum WP version required to run the Bootstrapper of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_min_wp(): string {
	return constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_WP' );
}

/**
 * Prints an error that the system requirements weren't met.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   InitializationFailure  $error       The initialization error that took place.
 * @param   PluginBase             $plugin      The plugin instance that failed to initialize.
 * @param   array                  $args        Associative array of other variables that should be made available in the template's context.
 */
function dws_wp_framework_output_initialization_error( InitializationFailure $error, PluginBase $plugin, array $args = array() ): void {
	if ( did_action( 'admin_notices' ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			'The initialization error message cannot be outputted after the admin_notices action has been already executed.',
			'1.0.0'
		);
	} else {
		add_action(
			'admin_notices',
			function() use ( $error, $plugin, $args ) {
				if ( $error instanceof PluginInitializationFailure ) {
					require_once __DIR__ . '/src/templates/initialization/initialization-error-plugin.php';
				} elseif ( $error instanceof FunctionalityInitializationFailure ) {
					require_once __DIR__ . '/src/templates/initialization/initialization-error-functionality.php';
				}
			}
		);
	}
}

/**
 * Registers the language files for the core's text domain.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
\add_action(
	'init',
	function() {
		load_plugin_textdomain(
			'dws-wp-framework-core',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/src/languages'
		);
	}
);

// Bootstrap the core (maybe)!
if ( dws_wp_framework_check_php_wp_requirements_met( dws_wp_framework_get_core_min_php(), dws_wp_framework_get_core_min_wp() ) ) {
	add_action(
		'plugins_loaded',
		function() {
			define(
				__NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT',
				apply_filters(
					'dws_wp_framework_core_init_status',
					defined( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_BOOTSTRAPPER_INIT' ) && DWS_WP_FRAMEWORK_BOOTSTRAPPER_INIT &&
					defined( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_HELPERS_INIT' ) && DWS_WP_FRAMEWORK_HELPERS_INIT &&
					defined( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_UTILITIES_INIT' ) && DWS_WP_FRAMEWORK_UTILITIES_INIT,
					__NAMESPACE__
				)
			);
		},
		PHP_INT_MIN + 100
	);
} else {
	define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT', false );
	dws_wp_framework_output_requirements_error( dws_wp_framework_get_core_name(), dws_wp_framework_get_core_version(), dws_wp_framework_get_core_min_php(), dws_wp_framework_get_core_min_wp() );
}
