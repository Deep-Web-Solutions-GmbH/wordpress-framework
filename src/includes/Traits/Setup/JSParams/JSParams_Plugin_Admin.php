<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\JSParams;

use DeepWebSolutions\Framework\Core\Abstracts\PluginBase;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Helpers\WordPress\Assets;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait that serves as an alternative to WP's native 'wp_localize_script' for when there might be no script
 * to attach it to exactly.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup\JSParams
 */
trait JSParams_Plugin_Admin {
	use Setup {
		setup as setup_jsparams_plugin_admin;
	}

	/**
	 * Enqueue the plugin's function for outputting the admin-side JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler  Instance of the hooks handler.
	 */
	public function setup_jsparams_plugin_admin( HooksHandler $hooks_handler ): void {
		if ( $this instanceof PluginBase ) {
			$hooks_handler->add_action( 'admin_head', $this, 'output_admin_js_object', PHP_INT_MIN );
		}
	}

	/**
	 * Outputs a javascript object in the <head></head> of the backend which can be used for tighter coupling between
	 * JS and PHP.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function output_admin_js_object(): void {
		if ( $this instanceof PluginBase ) {
			$object_name = $this->get_plugin_safe_slug();
			$params      = array_merge(
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ),
				apply_filters( 'dws_wp_framework_' . $this->get_plugin_safe_slug() . '_admin_js_params', array() )
			);

			echo Assets::get_javascript_from_string( "var $object_name = " . wp_json_encode( $params ) . ';' ); // phpcs:ignore
		}
	}
}
