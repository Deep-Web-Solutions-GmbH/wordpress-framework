<?php

namespace DeepWebSolutions\Framework\Core\Traits\Integrations;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Setupable;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable\IntegrateableOnInitialize;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping setupable objects at the end of an initialization routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Integrations
 */
trait SetupablesOnInitialize {
	use IntegrateableOnInitialize {
		integrate as integrate_setupables_on_initialize;
	}

	// region FIELDS AND CONSTANTS

	/**
	 * List of setupable objects to setup on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Setupable[]
	 */
	protected array $setupables_on_initialize = array();

	// endregion

	// region INHERITED METHODS

	/**
	 * After successful initialization, call the setup method of all registered setupable objects.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	public function integrate_setupables_on_initialize(): ?Exception {
		foreach ( $this->setupables_on_initialize as $setupable ) {
			$result = $setupable->setup();
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	// endregion

	// region METHODS

	/**
	 * Adds an object to the list of setupable objects to setup on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Setupable   $setupable      Setupable object to register with the class instance.
	 */
	public function register_setupable_on_initialization( Setupable $setupable ): void {
		$this->setupables_on_initialize[] = $setupable;
	}

	// endregion
}
