<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Inactive;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableInactive;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Assets as AssetsUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets of inactive instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Inactive
 */
trait AssetsInactive {
	use AssetsUtilities;
	use SetupableInactive {
		setup as setup_assets_inactive;
	}

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AssetsHandler   $assets_handler     Instance of the assets handler.
	 */
	public function setup_assets_inactive( AssetsHandler $assets_handler ): void {
		$this->enqueue_assets( $assets_handler );
	}
}
