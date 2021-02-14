<?php

namespace DeepWebSolutions\Framework\Core\Traits\Integrations;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\IntegrateableOnSetup;
use DeepWebSolutions\Framework\Utilities\Interfaces\Actions\Runnable;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping runnable objects at the end of a setup routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Integrations
 */
trait RunnablesOnSetup {
	use IntegrateableOnSetup {
		integrate as integrate_runnables_on_setup;
	}

	// region FIELDS AND CONSTANTS

	/**
	 * List of runnable objects to run on successful setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Runnable[]
	 */
	protected array $runnables_on_setup = array();

	// endregion

	// region INHERITED METHODS

	/**
	 * After successful setup, call the run method of all registered runnable objects.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	public function integrate_runnables_on_setup(): ?Exception {
		foreach ( $this->runnables_on_setup as $runnable ) {
			$result = $runnable->run();
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	// endregion

	// region METHODS

	/**
	 * Adds an object to the list of runnable objects to run on successful setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Runnable    $runnable   Runnable object to register with the class instance.
	 */
	public function register_runnable_on_setup( Runnable $runnable ): void {
		$this->runnables_on_setup[] = $runnable;
	}

	// endregion
}
