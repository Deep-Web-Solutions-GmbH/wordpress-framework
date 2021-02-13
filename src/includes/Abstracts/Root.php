<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\Properties\InexistentProperty;
use DeepWebSolutions\Framework\Core\Exceptions\Properties\ReadOnlyProperty;
use DeepWebSolutions\Framework\Helpers\PHP\Traits\Paths;
use DeepWebSolutions\Framework\Utilities\Interfaces\Identifiable;
use DeepWebSolutions\Framework\Utilities\Interfaces\Traits\Identity;
use DeepWebSolutions\Framework\Utilities\Services\LoggingService;
use DeepWebSolutions\Framework\Utilities\Services\Traits\Logging;
use Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating some of the most often required abilities of a class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
 */
abstract class Root implements Identifiable {
	use Identity;
	use Logging;
	use Paths;

	// region MAGIC METHODS

	/**
	 * Root constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   LoggingService  $logging_service    Instance of the plugin's logging service.
	 * @param   string|null     $root_id            The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|null     $root_name          The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 */
	public function __construct( LoggingService $logging_service, ?string $root_id = null, ?string $root_name = null ) {
		$this->set_logging_service( $logging_service );
		$this->set_instance_id( $root_id ?: hash( 'md5', static::class ) ); // phpcs:ignore
        $this->set_instance_public_name( $root_name ?: static::class ); // phpcs:ignore
	}

	/**
	 * Used for easily accessing global variables and the values of defined getters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   Name of the property that should be retrieved.
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @return  InexistentProperty|mixed
	 */
	public function __get( string $name ) {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			return $GLOBALS[ $name ];
		}

		return $this->get_logging_service()->log_event_and_return_exception( LogLevel::DEBUG, InexistentProperty::class, sprintf( 'Inexistent property: %s', $name ), 'framework' );
	}

	/**
	 * Used for writing data to global variables and to existent properties that have a setter defined.
	 *
	 * @param   string  $name   The name of the property that should be reassigned.
	 * @param   mixed   $value  The value that should be assigned to the property.
	 *
	 * @noinspection PhpDocRedundantThrowsInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return  mixed
	 * @throws  InexistentProperty  Thrown if there are no getters and no setter for the property, and a global variable also doesn't exist already.
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @throws  ReadOnlyProperty            Thrown if there is a getter for the property, but no setter.
	 * @version 1.0.0
	 *
	 * @since   1.0.0
	 */
	public function __set( string $name, $value ) {
		if ( method_exists( $this, ( $function = "set_{$name}" ) ) || method_exists( $this, ( $function = 'set' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}( $value );
		}

		if ( method_exists( $this, "get_{$name}" ) || method_exists( $this, 'get' . ucfirst( $name ) )
			|| method_exists( $this, "is_{$name}" ) || method_exists( $this, 'is' . ucfirst( $name ) ) ) {
			/** @noinspection PhpUnhandledExceptionInspection */ // phpcs:ignore
			throw $this->get_logging_service()->log_event_and_return_exception( LogLevel::DEBUG, ReadOnlyProperty::class, sprintf( 'Property %s is ready-only', $name ), 'framework' );
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			$GLOBALS[ $name ] = $value; // phpcs:ignore
			return true;
		}

		/** @noinspection PhpUnhandledExceptionInspection */ // phpcs:ignore
		throw $this->get_logging_service()->log_event_and_return_exception( LogLevel::DEBUG, InexistentProperty::class, sprintf( 'Inexistent property: %s', $name ), 'framework' );
	}

	/**
	 * Used for checking whether a global variable or a getter for a given property exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The name of the property that existence is being checked.
	 *
	 * @return  bool
	 */
	public function __isset( string $name ): bool {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		return isset( $GLOBALS[ $name ] );
	}

	// endregion

	// region SETTERS

	/**
	 * Set the unique persistent ID of the current class instance. Children classes can overwrite this to define their
	 * own logic for setting the root ID.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Identity::set_instance_id()
	 *
	 * @param   string  $instance_id    The value to be set.
	 */
	public function set_instance_id( string $instance_id ): void {
		$this->instance_id = $instance_id;
	}

	/**
	 * Set the public name of the current class instance. Children classes can overwrite this to define their own logic
	 * for setting the public name.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Identity::set_instance_public_name()
	 *
	 * @param   string  $instance_public_name   The value to be set.
	 */
	public function set_instance_public_name( string $instance_public_name ): void {
		$this->instance_public_name = $instance_public_name;
	}

	// endregion
}
