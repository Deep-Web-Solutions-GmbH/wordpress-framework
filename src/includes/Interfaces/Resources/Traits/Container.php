<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Resources\Traits;

use DI\Container as DIContainer;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for working with the Containerable interface.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Resources\Traits
 */
trait Container {
	// region FIELDS AND CONSTANTS

	/**
	 * Dependency injection container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     DIContainer
	 */
	protected DIContainer $di_container;

	// endregion

	// region GETTERS

	/**
	 * Gets the DI container instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  DIContainer
	 */
	public function get_container(): DIContainer {
		return $this->di_container;
	}

	// endregion

	// region SETTERS

	/**
	 * Sets the DI container instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   DIContainer     $container     The DI container instance to use from now on.
	 */
	public function set_container( DIContainer $container ): void {
		$this->di_container = $container;
	}

	// endregion

	// region HELPERS

	/**
	 * Returns the instance of a class from the container or null on failure.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $class  The name of the class to retrieve from the container.
	 *
	 * @return  object|null
	 */
	public function get_instance( string $class ): ?object {
		try {
			return $this->get_container()->get( $class );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	// endregion
}
