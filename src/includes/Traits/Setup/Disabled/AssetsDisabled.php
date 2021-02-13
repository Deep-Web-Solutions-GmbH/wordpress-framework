<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Disabled;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\SetupableDisabled;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Assets as AssetsUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets of disabled instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Disabled
 */
trait AssetsDisabled {
	use AssetsUtilities;
	use SetupableDisabled {
		setup as setup_assets_disabled;
	}

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AssetsHandler   $assets_handler     Instance of the assets handler.
	 */
	public function setup_assets_disabled( AssetsHandler $assets_handler ): void {
		$this->enqueue_assets( $assets_handler );
	}
}
