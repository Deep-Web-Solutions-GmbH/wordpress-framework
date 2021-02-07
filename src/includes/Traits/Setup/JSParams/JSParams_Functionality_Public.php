<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\JSParams;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for registering params with the frontend JS object.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup\JSParams
 */
trait JSParams_Functionality_Public {
	use Setup {
		setup as setup_jsparams_functionality_public;
	}

	/**
	 * Enqueue the child class' functions for registering params with the admin-side JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler  Instance of the hooks handler.
	 */
	public function setup_jsparams_functionality_public( HooksHandler $hooks_handler ): void {
		if ( $this instanceof Functionality ) {
			$hooks_handler->add_filter( 'dws_wp_framework_' . $this->get_plugin()->get_plugin_safe_slug() . '_public_js_params', $this, 'add_public_js_params' );
		}
	}

	/**
	 * Functionalities should define here the params they want to include in the frontend JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $params Params includes so far.
	 *
	 * @return  array
	 */
	abstract public function add_public_js_params( array $params ): array;
}
