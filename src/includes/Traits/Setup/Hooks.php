<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Hooks as HooksUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading hooks of active instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait Hooks {
	use HooksUtilities;
	use Setupable;

	/**
	 * Automagically call the hooks registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler      Instance of the hooks handler.
	 *
	 * @return  null
	 */
	public function setup_hooks( HooksHandler $hooks_handler ): ?SetupFailure {
		$this->set_hooks_handler( $hooks_handler );
		$this->register_hooks( $hooks_handler );
		return null;
	}
}
