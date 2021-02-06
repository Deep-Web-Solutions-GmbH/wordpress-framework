<?php

namespace DeepWebSolutions\Framework\Core\Traits\Abstracts;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;

/**
 * The trait that all other Assets traits need to use to include the asset handle function.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Abstracts
 */
trait Assets {
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
