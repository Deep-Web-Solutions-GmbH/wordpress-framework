<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable;

use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableTrait as FoundationsInitializableTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;

\defined( 'ABSPATH' ) || exit;

/**
 * Extends the foundations' initializable trait.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Foundations\Initializable
 */
trait InitializableTrait {
	// region TRAITS

	use FoundationsInitializableTrait { initialize as initialize_foundations; }

	// endregion

	// region METHODS

	/**
	 * Enhanced initialization logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailureException|null
	 */
	public function initialize(): ?InitializationFailureException {
		$result = $this->initialize_foundations();
		if ( \is_null( $result ) && ! \is_null( $result = $this->maybe_initialize_integrations() ) ) { // phpcs:ignore
			$this->is_initialized        = false;
			$this->initialization_result = $result;
		}

		return $this->initialization_result;
	}

	// endregion

	// region HELPERS

	/**
	 * Execute any potential initialization integration trait logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailureException|null
	 */
	protected function maybe_initialize_integrations(): ?InitializationFailureException {
		return $this->maybe_execute_extension_traits( InitializableIntegrationTrait::class, null, 'integrate' );
	}

	// endregion
}
