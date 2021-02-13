<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Containerable;
use DeepWebSolutions\Framework\Core\Interfaces\Setupable;
use DeepWebSolutions\Framework\Core\Interfaces\Initializable as IInitializable;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;
use DeepWebSolutions\Framework\Utilities\Interfaces\Runnable;

defined( 'ABSPATH' ) || exit;

/**
 * Simple trait for implementing an initialization logic.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Initializable
 */
trait Initialize {
	// region FIELDS AND CONSTANTS

	/**
	 * Whether the current object has been initialized yet or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected bool $initialized = false;

	// endregion

	// region GETTERS

	/**
	 * Returns whether the current object is initialized or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_initialized(): bool {
		return $this->initialized;
	}

	// endregion

	// region METHODS

	/**
	 * Simple initialization logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     IInitializable::initialize()
	 *
	 * @return  InitializationFailure|null
	 */
	public function initialize(): ?InitializationFailure {
		if ( true === $this->is_initialized() ) {
			return null;
		}

		// Perform any local initialization if necessary.
		if ( ! is_null( $result = $this->maybe_initialize_local() ) ) { // phpcs:ignore
			return $result;
		}
		if ( ! is_null( $result = $this->maybe_initialize_traits() ) ) { // phpcs:ignore
			return $result;
		}

		// Local initialization successful.
		$this->initialized = true;

		$this->maybe_setup();
		$this->maybe_run_runnables();

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
			$result = $this->initialize_local();
			if ( ! is_null( $result ) ) {
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
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( array_search( Initializable::class, Misc::class_uses_deep_list( $used_trait ), true ) !== false ) {
				foreach ( Misc::class_uses_deep( $used_trait ) as $trait_name => $used_traits ) {
					if ( array_search( Initializable::class, $used_traits, true ) !== false ) {
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

						break;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Execute any potential setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Setupable::setup()
	 */
	protected function maybe_setup(): void {
		if ( in_array( InitializeSetupable::class, Misc::class_uses_deep_list( $this ), true ) && $this instanceof Setupable ) {
			$this->setup();
		}
	}

	/**
	 * Run any potential runnable objects registered to perform on init.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function maybe_run_runnables(): void {
		if ( in_array( InitializeRunnable::class, Misc::class_uses_deep_list( $this ), true ) && property_exists( $this, 'runnable_on_init' ) ) {
			/** @var Runnable $runnable */ // phpcs:ignore
			foreach ( $this->runnable_on_init as $runnable ) {
				$runnable->run();
			}
		}
	}

	// endregion
}
