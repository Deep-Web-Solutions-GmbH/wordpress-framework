<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup
 */
trait Assets {
	use Setup {
		setup as setup_assets;
	}

	/**
	 * Enqueue the child class' asset enqueuing functions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_assets(): void {
		if ( $this instanceof Functionality ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_assets' );
			$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets' );
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

	/**
	 * Children classes should enqueue their public-side assets in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function enqueue_assets(): void;

	/**
	 * Returns a meaningful potentially unique handle for an asset.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The actual descriptor of the asset's purpose. Leave blank for default.
	 * @param   array   $extra  Further descriptor of the asset's purpose.
	 * @param   string  $root   Prepended to all asset handles inside the same class.
	 *
	 * @return  string
	 */
	public function get_asset_handle( string $name = '', array $extra = array(), string $root = '' ): string {
		return ( ! ( $this instanceof Functionality ) )
			? $name
			: str_replace(
				array( ' ', '/', '\\' ),
				array( '-', '', '' ),
				strtolower(
					join(
						'_',
						array_filter(
							array(
								$this->get_plugin()->get_plugin_slug(),
                                $root ?: $this->get_root_public_name(), // phpcs:ignore
								$name,
							),
							$extra
						)
					)
				)
			);
	}
}
