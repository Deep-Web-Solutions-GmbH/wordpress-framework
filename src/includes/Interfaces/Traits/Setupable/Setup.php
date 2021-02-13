<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable;

use DeepWebSolutions\Framework\Core\Interfaces\Containerable;
use DeepWebSolutions\Framework\Core\Interfaces\Setupable as ISetupable;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;
use DeepWebSolutions\Framework\Utilities\Interfaces\Activeable;
use DeepWebSolutions\Framework\Utilities\Interfaces\Disableable;

defined( 'ABSPATH' ) || exit;

/**
 * Simple trait for implementing a setup logic.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Setupable
 */
trait Setup {
	/**
	 * Simple setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     ISetupable::setup()
	 */
	public function setup(): void {
		if ( $this->maybe_check_disabled() ) {
			$this->maybe_setup_traits( SetupableDisabled::class );
		} else {
			if ( $this->maybe_check_active() ) {
				$this->maybe_setup_local();
				$this->maybe_setup_traits( Setupable::class );
			} else {
				$this->maybe_setup_traits( SetupableInactive::class );
			}
		}
	}

	/**
	 * Execute any potential local setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     SetupLocal::setup_local()
	 */
	protected function maybe_setup_local(): void {
		if ( in_array( SetupLocal::class, Misc::class_uses_deep_list( $this ), true ) && method_exists( $this, 'setup_local' ) ) {
			$this->setup_local();
		}
	}

	/**
	 * Execute any potential trait setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $trait  Name of the abstract trait which denotes a setup trait to search for.
	 */
	protected function maybe_setup_traits( string $trait ): void {
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( array_search( $trait, Misc::class_uses_deep_list( $used_trait ), true ) !== false ) {
				foreach ( Misc::class_uses_deep( $used_trait ) as $trait_name => $used_traits ) {
					if ( array_search( $trait, $used_traits, true ) !== false ) {
						$trait_boom  = explode( '\\', $trait_name );
						$method_name = 'setup' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );

						if ( method_exists( $this, $method_name ) ) {
							( $this instanceof Containerable )
								? $this->get_plugin()->get_container()->call( array( $this, $method_name ) )
								: $this->{$method_name}();
						}

						break;
					}
				}
			}
		}
	}

	/**
	 * Determine whether the instance is active or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return bool
	 */
	protected function maybe_check_active(): bool {
		if ( in_array( SetupActive::class, Misc::class_uses_deep_list( $this ), true ) && $this instanceof Activeable ) {
			return $this->is_active();
		}

		return true;
	}

	/**
	 * Potentially stop setup if instance is disabled.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return bool
	 */
	protected function maybe_check_disabled(): bool {
		if ( in_array( SetupDisabled::class, Misc::class_uses_deep_list( $this ), true ) && $this instanceof Disableable ) {
			return $this->is_disabled();
		}

		return false;
	}
}
