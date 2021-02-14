<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Inactive;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableInactive;
use DeepWebSolutions\Framework\Utilities\Handlers\ShortcodesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Shortcodes as ShortcodesUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading shortcodes of inactive instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Inactive
 */
trait ShortcodesInactive {
	use ShortcodesUtilities;
	use SetupableInactive;

	/**
	 * Automagically call the shortcodes registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ShortcodesHandler   $shortcodes_handler     Instance of the shortcodes handler.
	 *
	 * @return  null
	 */
	public function setup_shortcodes_inactive( ShortcodesHandler $shortcodes_handler ): ?SetupFailure {
		$this->register_shortcodes( $shortcodes_handler );
		return null;
	}
}
