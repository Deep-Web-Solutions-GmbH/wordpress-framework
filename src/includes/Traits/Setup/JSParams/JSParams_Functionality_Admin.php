<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\JSParams;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Abstracts\PluginBase;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for registering params with the admin-side JS object.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup\JSParams
 */
trait JSParams_Functionality_Admin {
	use Setup {
		setup as setup_jsparams_functionality_admin;
	}

	/**
	 * Enqueue the child class' functions for registering params with the admin-side JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_jsparams_functionality_admin(): void {
		if ( $this instanceof Functionality ) {
			$this->loader->add_filter( 'dws_wp_framework_' . $this->get_plugin()->get_plugin_safe_slug() . '_admin_js_params', $this, 'add_admin_js_params' );
		}
	}

	/**
	 * Functionalities should define here the params they want to include in the backend JS object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $params Params includes so far.
	 *
	 * @return  array
	 */
	abstract public function add_admin_js_params( array $params ): array;
}
