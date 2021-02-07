<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Utilities\Handlers\ShortcodesHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading shortcodes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup
 */
trait Shortcodes {
	use Setup {
		setup as setup_shortcodes;
	}

	/**
	 * Call the child class' shortcode defining function.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ShortcodesHandler   $shortcodes_handler     Instance of the shortcodes handler.
	 */
	public function setup_shortcodes( ShortcodesHandler $shortcodes_handler ): void {
		$this->define_shortcodes( $shortcodes_handler );
	}

	/**
	 * Children classes should define their shortcodes in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ShortcodesHandler   $shortcodes_handler     Instance of the shortcodes handler.
	 */
	abstract protected function define_shortcodes( ShortcodesHandler $shortcodes_handler ): void;
}
