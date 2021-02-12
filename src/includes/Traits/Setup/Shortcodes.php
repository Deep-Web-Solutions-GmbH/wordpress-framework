<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\ShortcodesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Shortcodes as ShortcodesUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading shortcodes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait Shortcodes {
	use ShortcodesUtilities;
	use Setupable {
		setup as setup_shortcodes;
	}

	/**
	 * Automagically call the shortcodes registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ShortcodesHandler   $shortcodes_handler     Instance of the shortcodes handler.
	 */
	public function setup_shortcodes( ShortcodesHandler $shortcodes_handler ): void {
		$this->register_shortcodes( $shortcodes_handler );
	}
}
