<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\IsActive;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Assets as AssetsUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets of active instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\IsActive
 */
trait Assets {
	use AssetsUtilities;
	use Setupable {
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
