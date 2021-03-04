<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable;

use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableTrait as FoundationsInitializableTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DeepWebSolutions\Framework\Helpers\DataTypes\Objects;
use DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

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
		if ( is_null( $result ) && ! is_null( $result = $this->maybe_initialize_integrations() ) ) { // phpcs:ignore
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
		if ( false !== array_search( InitializableIntegrationTrait::class, Objects::class_uses_deep_list( $this ), true ) ) {
			foreach ( Objects::class_uses_deep( $this ) as $trait_name => $deep_used_traits ) {
				if ( false === array_search( InitializableIntegrationTrait::class, $deep_used_traits, true ) ) {
					continue;
				}

				$trait_boom  = explode( '\\', $trait_name );
				$method_name = 'integrate' . strtolower( preg_replace( '/([A-Z]+)/', '_${1}', end( $trait_boom ) ) );
				$method_name = Strings::ends_with( $method_name, '_trait' ) ? str_replace( '_trait', '', $method_name ) : $method_name;

				if ( method_exists( $this, $method_name ) ) {
					$result = $this->{$method_name}();
					if ( ! is_null( $result ) ) {
						return new InitializationFailureException(
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
