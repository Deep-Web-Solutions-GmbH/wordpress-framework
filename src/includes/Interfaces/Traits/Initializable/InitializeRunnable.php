<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Utilities\Interfaces\Runnable;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for signaling that some local initialization needs to take place too.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Initializable
 */
trait InitializeRunnable {
	// region FIELDS AND CONSTANTS

	/**
	 * List of runnable objects to run on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Runnable[]
	 */
	protected array $runnable_on_init = array();

	// endregion

	// region METHODS

	/**
	 * Adds an object to the list of runnable objects to run on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Runnable    $runnable   Runnable object to register with this plugin instance.
	 */
	public function register_runnable( Runnable $runnable ): void {
		$this->runnable_on_init[] = $runnable;
	}

	// endregion
}
