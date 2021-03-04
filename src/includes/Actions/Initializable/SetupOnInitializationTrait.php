<?php

namespace DeepWebSolutions\Framework\Core\Actions\Initializable;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable\InitializableIntegrationTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping the 'setup' method at the end of the initialization routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Initializable
 */
trait SetupOnInitializationTrait {
	use InitializableIntegrationTrait;

	/**
	 * After successful initialization, call the 'setup' method of the using class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailureException|null
	 */
	public function integrate_setup_on_initialization(): ?SetupFailureException {
		return ( $this instanceof SetupableInterface )
			? $this->setup()
			: null;
	}
}
