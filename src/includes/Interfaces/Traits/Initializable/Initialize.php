<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;
use DeepWebSolutions\Framework\Helpers\PHP\Misc;

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

		if ( in_array( InitializeLocal::class, Misc::class_uses_deep( $this ), true ) && method_exists( $this, 'initialize_local' ) ) {
			$result = $this->initialize_local();
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		$this->initialized = true;
		return null;
	}

	// endregion
}
