<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States\SetupableDisabledTrait;
use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States\SetupableInactiveTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupableTrait as FoundationsSetupableTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupFailureException;
use DeepWebSolutions\Framework\Foundations\States\ActiveableInterface;
use DeepWebSolutions\Framework\Foundations\States\DisableableInterface;
use DeepWebSolutions\Framework\Helpers\DataTypes\Objects;

\defined( 'ABSPATH' ) || exit;

/**
 * Extends the foundations' setupable trait.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Foundations\Setupable
 */
trait SetupableTrait {
	// region TRAITS

	use FoundationsSetupableTrait { setup as setup_foundations; }

	// endregion

	// region METHODS

	/**
	 * Enhanced setup logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailureException|null
	 */
	public function setup(): ?SetupFailureException {
		$this->setup_result = null;

		if ( true === $this->should_setup() ) {
			$result = $this->setup_foundations();
			if ( \is_null( $result ) && ! \is_null( $result = $this->maybe_setup_integrations() ) ) { // phpcs:ignore
				$this->is_setup     = false;
				$this->setup_result = new SetupFailureException(
					$result->getMessage(),
					$result->getCode(),
					$result
				);
			}
		}

		return $this->setup_result;
	}

	// endregion

	// region HELPERS

	/**
	 * Maybe skip setup based on instance state.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	protected function should_setup(): bool {
		$should_setup = true;

		if ( $this instanceof DisableableInterface ) {
			$should_setup = ( ! $this->is_disabled() || Objects::has_trait_deep( SetupableDisabledTrait::class, $this ) );
		}

		if ( $should_setup && $this instanceof ActiveableInterface ) {
			$should_setup = ( $this->is_active() || Objects::has_trait_deep( SetupableInactiveTrait::class, $this ) );
		}

		return $should_setup;
	}

	/**
	 * Execute any potential setup integration trait logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailureException|null
	 */
	protected function maybe_setup_integrations(): ?SetupFailureException {
		return $this->maybe_execute_extension_traits( SetupableIntegrationTrait::class, null, 'integrate' );
	}

	// endregion
}
