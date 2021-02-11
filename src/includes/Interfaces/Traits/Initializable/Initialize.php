<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;
use DeepWebSolutions\Framework\Utilities\Interfaces\Runnable;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for signaling that some local initialization needs to take place too.
 *
 * @since   1.0.0
 * @version 1.0.0
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
	 * @see     Initializable::initialize()
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

		// Local initialization successful.
		$this->initialized = true;
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
		if ( in_array( InitializeLocal::class, Misc::class_uses_deep( $this ), true ) && method_exists( $this, 'initialize_local' ) ) {
			$result = $this->initialize_local();
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Run any potential runnable objects registered to perform on init.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function maybe_run_runnables(): void {
		if ( in_array( InitializeRunnable::class, Misc::class_uses_deep( $this ), true ) && property_exists( $this, 'runnable_on_init' ) ) {
			/** @var Runnable $runnable */ // phpcs:ignore
			foreach ( $this->runnable_on_init as $runnable ) {
				$runnable->run();
			}
		}
	}

	// endregion
}
