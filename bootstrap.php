<?php
/**
 * The DWS WordPress Framework Core bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\wordpress-framework-core
 * @author              Deep Web Solutions GmbH
 * @copyright           2020 Deep Web Solutions GmbH
 * @license             GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         DWS WordPress Framework Core
 * Description:         A set of related classes to kick start WordPress development.
 * Version:             1.0.0
 * Author:              Deep Web Solutions GmbH
 * Author URI:          https://www.deep-web-solutions.de
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         dws-wp-framework-core
 * Domain Path:         /src/languages
 */

namespace DeepWebSolutions\Framework\Core;

use function DeepWebSolutions\Framework\Bootstrap\dws_wp_framework_check_php_wp_requirements_met;
use function DeepWebSolutions\Framework\Bootstrap\dws_wp_framework_output_requirements_error;
use const DeepWebSolutions\Framework\Bootstrap\DWS_WP_FRAMEWORK_BOOTSTRAPPER_INIT;
use const DeepWebSolutions\Framework\Helpers\DWS_WP_FRAMEWORK_HELPERS_INIT;
use const DeepWebSolutions\Framework\Utilities\DWS_WP_FRAMEWORK_UTILITIES_INIT;

if ( ! defined( 'ABSPATH' ) ) {
	return; // Since this file is autoloaded by Composer, 'exit' breaks all external dev tools.
}

// Start by autoloading dependencies and defining a few functions for running the core.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php'; // The conditional check makes the whole thing compatible with Composer-based WP management.
}

// Define minimum environment requirements.
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_VERSION', 'v1.0.0' );
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_PHP', '7.4' );
define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_WP', '5.4' );

// The following settings can be overwritten in a configuration file or could be set by other versions as well.
defined( 'DWS_WP_FRAMEWORK_CORE_NAME' ) || define( 'DWS_WP_FRAMEWORK_CORE_NAME', DWS_WP_FRAMEWORK_WHITELABEL_NAME . ': Framework Core' );

// Bootstrap (maybe)! // phpcs:ignore
$dws_core_version         = constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_VERSION' );
$dws_core_min_php_version = constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_PHP' );
$dws_core_min_wp_version  = constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_WP' );

if ( dws_wp_framework_check_php_wp_requirements_met( $dws_core_min_php_version, $dws_core_min_wp_version ) ) {
	add_action(
		'plugins_loaded',
		function() {
			define(
				__NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT',
				DWS_WP_FRAMEWORK_BOOTSTRAPPER_INIT && DWS_WP_FRAMEWORK_UTILITIES_INIT && DWS_WP_FRAMEWORK_HELPERS_INIT
			);
		},
		PHP_INT_MIN + 100
	);
} else {
	define( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT', false );
	dws_wp_framework_output_requirements_error( DWS_WP_FRAMEWORK_CORE_NAME, $dws_core_version, $dws_core_min_php_version, $dws_core_min_wp_version );
}
