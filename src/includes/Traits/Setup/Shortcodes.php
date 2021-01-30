<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\SetupTrait;
use DeepWebSolutions\Framework\Utilities\Loader;

/**
 * Functionality trait for loading shortcodes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits
 */
trait Shortcodes {
	use SetupTrait {
		setup as setup_shortcodes;
	}

	/**
	 * Call the child class' shortcode defining function.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_shortcodes(): void {
		if ( $this instanceof Functionality ) {
			$this->define_shortcodes( $this->loader );
		}
	}

	/**
	 * Children classes should define their shortcodes in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Loader  $loader     Instance of the loader class, for convenience.
	 */
	abstract protected function define_shortcodes( Loader $loader ): void;

	/**
	 * Returns a meaningful probably unique name for an internal hook.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name       The actual descriptor of the hook's purpose.
	 * @param   array   $extra      Further descriptor of the hook's purpose.
	 * @param   string  $root       Prepended to all hooks inside the same class.
	 *
	 * @return  string
	 */
	public function get_hook_name( string $name, array $extra = array(), string $root = '' ): string {
		return ( ! ( $this instanceof Functionality ) )
			? $name
			: str_replace(
				array( ' ', '/', '\\' ),
				array( '-', '', '' ),
				strtolower(
					join(
						'_',
						array_filter(
							array_merge(
								array(
									$this->get_plugin()->get_plugin_slug(),
                                    $root ?: $this->get_root_public_name(), // phpcs:ignore
									$name,
								),
								$extra
							)
						)
					)
				)
			);
	}
}
