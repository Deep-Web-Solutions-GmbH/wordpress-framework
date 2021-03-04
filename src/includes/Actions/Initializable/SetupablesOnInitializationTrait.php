<?php

namespace DeepWebSolutions\Framework\Core\Actions\Initializable;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable\InitializableIntegrationTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping the 'setup' method of setupable objects at the end of the initialization routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Initializable
 */
trait SetupablesOnInitializationTrait {
	// region TRAITS

	use InitializableIntegrationTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * List of setupable objects to setup on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     SetupableInterface[]
	 */
	protected array $setupables_on_initialize = array();

	// endregion

	// region METHODS

	/**
	 * After successful initialization, call the setup method of the using class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailureException|null
	 */
	public function integrate_setupables_on_initialization(): ?SetupFailureException {
		foreach ( $this->setupables_on_initialize as $setupable ) {
			$result = $setupable->setup();
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Adds an object to the list of setupable objects to setup on successful initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   SetupableInterface      $setupable      Setupable object to register with the class instance.
	 *
	 * @return  self
	 */
	public function register_setupable_on_initialization( SetupableInterface $setupable ): self {
		$this->setupables_on_initialize[] = $setupable;
		return $this;
	}

	// endregion
}
