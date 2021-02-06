<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\JSParams;

use DeepWebSolutions\Framework\Core\Abstracts\PluginBase;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait that serves as an alternative to WP's native 'wp_localize_script' for when there might be no script
 * to attach it to exactly.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup\JSParams
 */
trait JSParams_Plugin_Public {
	use Setup {
		setup as setup_jsparams_plugin_public;
	}

	/**
	 * Enqueue the plugin's function for outputting the frontend JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_jsparams_plugin_public(): void {
		if ( $this instanceof PluginBase ) {
			$this->loader->add_action( 'wp_head', $this, 'output_public_js_object', PHP_INT_MIN );
		}
	}

	/**
	 * Outputs a javascript object in the <head></head> of the frontend which can be used for tighter coupling between
	 * JS and PHP.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function output_public_js_object(): void {
		if ( $this instanceof PluginBase ) {
			$object_name = $this->get_plugin_safe_slug();
			$params      = array_merge(
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ),
				apply_filters( 'dws_wp_framework_' . $this->get_plugin_safe_slug() . '_public_js_params', array() )
			);

			echo esc_js( "var $object_name = " . wp_json_encode( $params ) . ';' );
		}
	}
}
