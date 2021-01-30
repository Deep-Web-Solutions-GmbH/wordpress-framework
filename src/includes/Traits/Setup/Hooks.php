<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup;
use DeepWebSolutions\Framework\Utilities\Loader;

/**
 * Functionality trait for loading hooks.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Setup
 */
trait Hooks {
	use Setup {
		setup as setup_hooks;
	}

	/**
	 * Call the child class' hooks defining function.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_hooks(): void {
		if ( $this instanceof Functionality ) {
			$this->define_hooks( $this->loader );
		}
	}

	/**
	 * Children classes should define their hooks in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Loader  $loader     Instance of the loader class, for convenience.
	 */
	abstract protected function define_hooks( Loader $loader ): void;

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
