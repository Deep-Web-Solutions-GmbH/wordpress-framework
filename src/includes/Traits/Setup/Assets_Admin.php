<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Assets;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets on the admin-side.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup
 */
trait Assets_Admin {
	use Assets;
	use Setup {
		setup as setup_assets_admin;
	}

	/**
	 * Enqueue the child class' asset enqueuing functions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_assets_admin(): void {
		if ( $this instanceof Functionality ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_assets' );
		}
	}

	/**
	 * Children classes should enqueue their admin-side assets in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $hook   Name of the php file currently being rendered.
	 */
	abstract public function admin_enqueue_assets( string $hook ): void;
}
