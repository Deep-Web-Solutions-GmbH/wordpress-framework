<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableDisabled;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableInactive;
use DeepWebSolutions\Framework\Core\Interfaces\Resources\Containerable;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\IsActiveable;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\IsDisableable;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for working with the Setupable interface.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Setupable
 */
trait Setup {
	// region FIELDS AND CONSTANTS

	/**
	 * Whether the using instance is setup or not. Null if not decided yet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool|null
	 */
	protected ?bool $is_setup = null;

	// endregion

	// region GETTERS

	/**
	 * Returns whether the using instance is setup or not. Null if not decided yet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool|null
	 */
	public function is_setup(): ?bool {
		return $this->is_setup;
	}

	// endregion

	// region METHODS

	/**
	 * Simple setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailure|null
	 */
	public function setup(): ?SetupFailure {
		if ( is_null( $this->is_setup ) ) {
			$setup_trait = $this->check_setup_state();

			if ( ! is_null( $result = $this->maybe_setup_traits( $setup_trait ) ) ) { // phpcs:ignore
				$this->is_setup = false;
				return $result;
			}
			if ( ! is_null( $result = $this->maybe_setup_local() ) ) { // phpcs:ignore
				$this->is_setup = false;
				return $result;
			}

			$this->is_setup = true;

			if ( ! is_null( $result = $this->maybe_setup_integrations() ) ) { // phpcs:ignore
				return $result;
			}
		}

		return null;
	}

	/**
	 * Execute any potential trait setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $trait  Name of the abstract trait which denotes a setup trait to search for.
	 *
	 * @return  SetupFailure|null
	 */
	protected function maybe_setup_traits( string $trait ): ?SetupFailure {
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( false === array_search( $trait, Misc::class_uses_deep_list( $used_trait ), true ) ) {
				continue;
			}

			foreach ( Misc::class_uses_deep( $used_trait ) as $trait_name => $recursive_used_traits ) {
				if ( false === array_search( $trait, $recursive_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'setup' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );

				if ( method_exists( $this, $method_name ) ) {
					$result = ( $this instanceof Containerable )
						? $this->get_plugin()->get_container()->call( array( $this, $method_name ) )
						: $this->{$method_name}();

					if ( ! is_null( $result ) ) {
						return $result;
					}
				}

				break;
			}
		}

		return null;
	}

	/**
	 * Execute any potential local setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     SetupLocal::setup_local()
	 *
	 * @return  SetupFailure|null
	 */
	protected function maybe_setup_local(): ?SetupFailure {
		if ( in_array( SetupLocal::class, Misc::class_uses_deep_list( $this ), true ) && method_exists( $this, 'setup_local' ) ) {
			return $this->setup_local();
		}

		return null;
	}

	/**
	 * Execute any potential setup integration trait logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailure|null
	 */
	protected function maybe_setup_integrations(): ?SetupFailure {
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( false === array_search( IntegrateableOnSetup::class, Misc::class_uses_deep_list( $used_trait ), true ) ) {
				continue;
			}

			foreach ( Misc::class_uses_deep( $used_trait ) as $trait_name => $recursive_used_traits ) {
				if ( false === array_search( IntegrateableOnSetup::class, $recursive_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'integrate' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );

				if ( method_exists( $this, $method_name ) ) {
					$result = ( $this instanceof Containerable )
						? $this->get_plugin()->get_container()->call( array( $this, $method_name ) )
						: $this->{$method_name}();

					if ( ! is_null( $result ) ) {
						return new SetupFailure(
							$result->getMessage(),
							$result->getCode(),
							$result
						);
					}
				}

				break;
			}
		}

		return null;
	}

	// endregion

	// region HELPERS

	/**
	 * Based on instance state, decide which setup routine should be triggered. By default, the active instance routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	protected function check_setup_state(): string {
		if ( $this instanceof IsDisableable && $this->is_disabled() ) {
			return SetupableDisabled::class;
		} elseif ( $this instanceof IsActiveable && ! $this->is_active() ) {
			return SetupableInactive::class;
		}

		return Setupable::class;
	}

	// endregion
}
