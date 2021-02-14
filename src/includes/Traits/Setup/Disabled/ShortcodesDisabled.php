<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Disabled;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableDisabled;
use DeepWebSolutions\Framework\Utilities\Handlers\ShortcodesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Shortcodes as ShortcodesUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for loading shortcodes of disabled instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Disabled
 */
trait ShortcodesDisabled {
	use ShortcodesUtilities;
	use SetupableDisabled;

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
	public function setup_shortcodes_disabled( ShortcodesHandler $shortcodes_handler ): ?SetupFailure {
		$this->register_shortcodes( $shortcodes_handler );
		return null;
	}
}
