<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Assets as AssetsUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets on the frontend.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait Assets {
	use AssetsUtilities;
	use Setup {
		setup as setup_assets;
	}

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AssetsHandler   $assets_handler     Instance of the assets handler.
	 */
	public function setup_assets( AssetsHandler $assets_handler ): void {
		$this->enqueue_assets( $assets_handler );
	}
}
