<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Traits\Abstracts\Assets;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets on the frontend.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup
 */
trait Assets_Public {
	use Assets;
	use Setup {
		setup as setup_assets_public;
	}

	/**
	 * Enqueue the child class' asset enqueuing functions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksHandler    $hooks_handler  Instance of the hooks handler.
	 */
	public function setup_assets_public( HooksHandler $hooks_handler ): void {
		$hooks_handler->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets' );
	}

	/**
	 * Children classes should enqueue their public-side assets in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function enqueue_assets(): void;
}
