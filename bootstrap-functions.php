<?php
/**
 * Defines module-specific getters and functions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core
 *
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace DeepWebSolutions\Framework;

use DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionalityRoot;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;

\defined( 'ABSPATH' ) || exit;

/**
 * Returns the whitelabel name of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_name() {
	return \constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_NAME' );
}

/**
 * Returns the version of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_version() {
	return \constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_VERSION' );
}

/**
 * Returns the base filesystem path to the current file.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_base_path() {
	return constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_BASE_PATH' );
}

/**
 * Returns the minimum PHP version required to run the Bootstrapper of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_min_php() {
	return \constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_PHP' );
}

/**
 * Returns the minimum WP version required to run the Bootstrapper of the framework's core within the context of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_wp_framework_get_core_min_wp() {
	return \constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_MIN_WP' );
}

/**
 * Returns whether the core package has managed to initialize successfully or not in the current environment.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool
 */
function dws_wp_framework_get_core_init_status() {
	return \defined( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT' ) && \constant( __NAMESPACE__ . '\DWS_WP_FRAMEWORK_CORE_INIT' );
}

/**
 * Prints an error that the system requirements weren't met.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   InitializationFailureException      $error      The initialization error that took place.
 * @param   AbstractPluginFunctionalityRoot     $plugin     The plugin instance that failed to initialize.
 * @param   array                               $args       Associative array of other variables that should be made available in the template's context.
 */
function dws_wp_framework_output_initialization_error( InitializationFailureException $error, AbstractPluginFunctionalityRoot $plugin, array $args = array() ) {
	if ( \did_action( 'admin_notices' ) ) {
		\_doing_it_wrong(
			__FUNCTION__,
			'The initialization error message cannot be outputted after the admin_notices action has been already executed.',
			'1.0.0'
		);
	} else {
		\add_action(
			'admin_notices',
			function() use ( $error, $plugin, $args ) {
				require_once __DIR__ . '/src/templates/initialization/error.php';
			}
		);
	}
}
