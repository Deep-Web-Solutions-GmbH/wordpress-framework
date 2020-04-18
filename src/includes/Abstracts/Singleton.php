<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * A template for a singleton class. Shouldn't be needed if using proper dependency injection.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Abstracts
 */
abstract class Singleton {
	// region FIELDS

	/**
	 * Maintains a list of all singleton instances created.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static array $instances = array();

	// endregion

	// region MAGIC METHODS

	/**
	 * Singleton constructor. Children classes can overwrite this and perform custom actions.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   ...$args    Array of all the arguments that the constructor of the end class needs.
	 */
	protected function __construct( ...$args ) { }

	/**
	 * Prevent cloning.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function __clone() { }

	/**
	 * Prevent serialization.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function __sleep() { }

	/**
	 * Prevent unserialization.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function __wakeup() { }

	// endregion

	// region METHODS

	/**
	 * Returns a singleton instance of the calling class.
	 *
	 * @since   1.0.0
	 * @version 2.0.0
	 *
	 * @return  $this   The instance of the calling class.
	 */
	final public static function get_instance() {
		self::maybe_initialize_singleton( ...func_get_args() );
		return self::$instances[ static::class ];
	}

	/**
	 * If the singleton has not been initialized yet, this function instantiates it. Also allows passing
	 * of parameters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public static function maybe_initialize_singleton() {
		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = new static( ...func_get_args() );
		}
	}

	// endregion
}
