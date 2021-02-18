<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\Assets as AssetsUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets of active instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait Assets {
	use AssetsUtilities;
	use Setupable;

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AssetsHandler   $assets_handler     Instance of the assets handler.
	 *
	 * @return  null
	 */
	public function setup_assets( AssetsHandler $assets_handler ): ?SetupFailure {
		$this->set_assets_handler( $assets_handler );
		$this->enqueue_assets( $assets_handler );
		return null;
	}
}
