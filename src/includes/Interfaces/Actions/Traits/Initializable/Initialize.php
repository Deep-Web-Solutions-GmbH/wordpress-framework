<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\InitializationFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Resources\Containerable;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for working with the Initializable interface.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Initializable
 */
trait Initialize {
	// region FIELDS AND CONSTANTS

	/**
	 * Whether the using instance is initialized or not. Null if not decided yet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool|null
	 */
	protected ?bool $is_initialized = null;

	// endregion

	// region GETTERS

	/**
	 * Returns whether the using instance is initialized or not. Null if not decided yet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool|null
	 */
	public function is_initialized(): ?bool {
		return $this->is_initialized;
	}

	// endregion

	// region METHODS

	/**
	 * Simple initialization logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailure|null
	 */
	public function initialize(): ?InitializationFailure {
		if ( is_null( $this->is_initialized ) ) {
			if ( ! is_null( $result = $this->maybe_initialize_traits() ) ) { // phpcs:ignore
				$this->is_initialized = false;
				return $result;
			}
			if ( ! is_null( $result = $this->maybe_initialize_local() ) ) { // phpcs:ignore
				$this->is_initialized = false;
				return $result;
			}

			$this->is_initialized = true;

			if ( ! is_null( $result = $this->maybe_initialize_integrations() ) ) { // phpcs:ignore
				return $result;
			}
		}

		return null;
	}

	/**
	 * Execute any potential trait initialization logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailure|null
	 */
	protected function maybe_initialize_traits(): ?InitializationFailure {
		if ( false !== array_search( Initializable::class, Misc::class_uses_deep_list( $this ), true ) ) {
			foreach ( Misc::class_uses_deep( $this ) as $trait_name => $recursive_used_traits ) {
				if ( false === array_search( Initializable::class, $recursive_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'initialize' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );

				if ( method_exists( $this, $method_name ) ) {
					$result = ( $this instanceof Containerable )
						? $this->get_plugin()->get_container()->call( array( $this, $method_name ) )
						: $this->{$method_name}();

					if ( ! is_null( $result ) ) {
						return $result;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Execute any potential local initialization logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     InitializeLocal::initialize_local()
	 *
	 * @return  InitializationFailure|null
	 */
	protected function maybe_initialize_local(): ?InitializationFailure {
		if ( in_array( InitializeLocal::class, Misc::class_uses_deep_list( $this ), true ) && method_exists( $this, 'initialize_local' ) ) {
			return $this->initialize_local();
		}

		return null;
	}

	/**
	 * Execute any potential initialization integration trait logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	protected function maybe_initialize_integrations(): ?InitializationFailure {
		if ( false !== array_search( IntegrateableOnInitialize::class, Misc::class_uses_deep_list( $this ), true ) ) {
			foreach ( Misc::class_uses_deep( $this ) as $trait_name => $recursive_used_traits ) {
				if ( false === array_search( IntegrateableOnInitialize::class, $recursive_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'integrate' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );

				if ( method_exists( $this, $method_name ) ) {
					$result = ( $this instanceof Containerable )
						? $this->get_plugin()->get_container()->call( array( $this, $method_name ) )
						: $this->{$method_name}();

					if ( ! is_null( $result ) ) {
						return new InitializationFailure(
							$result->getMessage(),
							$result->getCode(),
							$result
						);
					}
				}
			}
		}

		return null;
	}

	// endregion
}
