<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable;

use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupableTrait as FoundationsSetupableTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\SetupFailureException;
use DeepWebSolutions\Framework\Helpers\DataTypes\Objects;
use DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

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
		$result = $this->setup_foundations();
		if ( is_null( $result ) && ! is_null( $result = $this->maybe_setup_integrations() ) ) { // phpcs:ignore
			$this->is_setup     = false;
			$this->setup_result = $result;
		}

		return $this->setup_result;
	}

	// endregion

	// region HELPERS

	/**
	 * Execute any potential setup integration trait logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailureException|null
	 */
	protected function maybe_setup_integrations(): ?SetupFailureException {
		if ( false !== array_search( SetupableIntegrationTrait::class, Objects::class_uses_deep_list( $this ), true ) ) {
			foreach ( Objects::class_uses_deep( $this ) as $trait_name => $deep_used_traits ) {
				if ( false === array_search( SetupableIntegrationTrait::class, $deep_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'integrate' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );
				$method_name = Strings::ends_with( $method_name, '_trait' ) ? str_replace( '_trait', '', $method_name ) : $method_name;

				if ( method_exists( $this, $method_name ) ) {
					$result = $this->{$method_name}();
					if ( ! is_null( $result ) ) {
						return new SetupFailureException(
							$result->getMessage(),
							$result->getCode(),
							$result
						);
					}
				}
			}
		}

		return null;
	}

	// endregion
}
