<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Hooks as HooksUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading hooks.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait Hooks {
	use HooksUtilities;
	use Setupable {
		setup as setup_hooks;
	}

	/**
	 * Automagically call the hooks registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler      Instance of the hooks handler.
	 */
	public function setup_hooks( HooksHandler $hooks_handler ): void {
		$this->register_hooks( $hooks_handler );
	}
}
