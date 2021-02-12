<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Containerable;
use DeepWebSolutions\Framework\Core\Interfaces\Initializable;
use DeepWebSolutions\Framework\Core\Interfaces\Setupable;
use DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable\Initialize;
use DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable\InitializeSetupable;
use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\Setup;
use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\SetupActive;
use DeepWebSolutions\Framework\Utilities\Interfaces\Activeable;
use DeepWebSolutions\Framework\Utilities\Interfaces\Traits\Activeable\Active;
use DeepWebSolutions\Framework\Utilities\Interfaces\Traits\Identity;
use DI\Container;
use Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the piping required for a DWS functionality.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
 *
 * @see     Root
 */
abstract class Functionality extends Root implements Activeable, Containerable, Initializable, Setupable {
	use Active {
		is_active as is_active_trait;
	}
	use Initialize;
	use InitializeSetupable;
	use Setup;
	use SetupActive;

	// region FIELDS AND CONSTANTS

	/**
	 * The parent of this functionality, if it exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Functionality|null
	 */
	protected ?Functionality $parent = null;

	/**
	 * The children of this functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Functionality[]
	 */
	protected array $children = array();

	/**
	 * The recursive depth of the current functionality. The main plugin instance is 0.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     int
	 */
	protected int $functionality_depth = 0;

	// endregion

	// region GETTERS

	/**
	 * Gets the static instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Containerable::get_container()
	 *
	 * @return  Container
	 */
	public function get_container(): Container {
		return $this->get_plugin()->get_container();
	}

	/**
	 * Checks whether the current functionality has a parent or not. Typically the only functionality without a parent
	 * is the main plugin class itself.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool    True if the functionality has a parent, false otherwise.
	 */
	public function has_parent(): bool {
		return null !== $this->parent;
	}

	/**
	 * Gets the parent of the current functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Functionality|null
	 */
	public function get_parent(): ?Functionality {
		return $this->parent;
	}

	/**
	 * Method inspired by jQuery's 'closest' for getting the first parent functionality that is an instance of a given class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $class  The name of the class of the searched-for parent functionality.
	 *
	 * @return  Functionality|null
	 */
	public function get_closest( string $class ): ?Functionality {
		$current = $this;
		while ( $current->has_parent() && is_a( $current, $class ) ) {
			$current = $current->get_parent();
		}

		if ( $current === $this || ! is_a( $current, $class ) ) {
			return null;
		}

		return $current;
	}

	/**
	 * Checks whether the current functionality has any children or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function has_children(): bool {
		return ! empty( $this->children );
	}

	/**
	 * Returns all the children functionalities of this functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	public function get_children(): array {
		return $this->children;
	}

	/**
	 * Returns the depth of the current functionality in the tree of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  int
	 */
	public function get_depth(): int {
		return $this->functionality_depth;
	}

	// endregion

	// region SETTERS

	/**
	 * Sets the parent of the functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Functionality   $parent     The parent functionality of the current instance.
	 */
	public function set_parent( Functionality $parent ): void {
		$this->parent              = $parent;
		$this->functionality_depth = $parent->get_depth() + 1;
	}

	/**
	 * Sets the plugin instance that the current functionality belongs to.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Identity::set_plugin()
	 */
	public function set_plugin(): void {
		$current = $this;
		while ( $current->has_parent() ) {
			$current = $current->get_parent();
		}

		if ( $current instanceof PluginBase ) {
			$this->plugin = $current;
		} else {
			$this->get_logging_service()->log_event(
				LogLevel::ERROR,
				sprintf(
					'Found functionality without parent inside plugin tree. Functionality name: %s',
					$current->get_instance_public_name()
				),
				'framework'
			);
		}
	}

	// endregion

	// region METHODS

	/**
	 * The starting point of instance logic. Loads and initializes children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Initializable::initialize()
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	public function initialize(): ?FunctionalityInitializationFailure {
		$this->set_plugin();

		// Perform any local initialization, if applicable.
		if ( ! is_null( $result = $this->maybe_initialize_local() ) ) { // phpcs:ignore
			return new FunctionalityInitializationFailure( $result->getMessage() );
		}
		if ( ! is_null( $result = $this->maybe_initialize_traits() ) ) { // phpcs:ignore
			return new FunctionalityInitializationFailure( $result->getMessage() );
		}

		// Instantiate all children and properly set parent-child relations.
		if ( ! is_null( $result = $this->load_children_functionalities() ) ) { // phpcs:ignore
			return $result;
		}

		// Initialize the children as well.
		foreach ( $this->get_children() as $child ) {
			if ( ! is_null( $result = $this->try_initialization( $child ) ) ) { // phpcs:ignore
				return $result;
			}
		}

		// Self-initialization and the initialization of the child tree was successful.
		$this->initialized = true;

		$this->maybe_setup();
		$this->maybe_run_runnables();

		return null;
	}

	/**
	 * Children classes should use this function to lazy-load their children functionalities. Possibly conditionally.
	 * MUST use the function 'add_child' for everything to work properly.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::add_child()
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function load_children_functionalities(): ?FunctionalityInitializationFailure {
		return null;
	}

	/**
	 * Checks whether the current functionality is active, and also all of its ancestors.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Activeable::is_active()
	 * @see     Active::is_active()
	 *
	 * @return  bool
	 */
	public function is_active(): bool {
		if ( is_null( $this->active ) ) {
			$this->active = ( $this->has_parent() && ! $this->get_parent()->is_active() )
				? false
				: $this->is_active_trait();
		}

		return $this->active;
	}

	// endregion

	// region HELPERS

	/**
	 * Uses the DI container to create an instance of a class and adds it to the list of children classes for this
	 * functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $class  Name of the class that should be instantiated.
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function add_child( string $class ): ?FunctionalityInitializationFailure {
		try {
			$child = $this->get_plugin()->get_container()->get( $class );
		} catch ( \Exception $e ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return $this->get_logging_service()->log_event_and_return_exception(
				LogLevel::ERROR,
				FunctionalityInitializationFailure::class,
				sprintf(
					'Failed to instantiate class %1$s. Error message: %2$s',
					$class,
					$e->getMessage()
				),
				'framework'
			);
		}

		if ( ! ( $child instanceof Functionality ) ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return $this->get_logging_service()->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				sprintf(
					'Children of functionalities must be functionalities too! Cannot add instance of type %1$s as child to instance of type %2$s.',
					$class,
					static::class
				),
				'1.0.0',
				FunctionalityInitializationFailure::class,
				LogLevel::ERROR,
				'framework'
			);
		}

		if ( ! is_null( $child->get_parent() ) ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return $this->get_logging_service()->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				sprintf(
					'Child instance %1$s already has a parent. Cannot set parent as %2$s.',
					$class,
					static::class
				),
				'1.0.0',
				FunctionalityInitializationFailure::class,
				LogLevel::ERROR,
				'framework'
			);
		}

		if ( $child === $this ) {
			return $this->get_logging_service()->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				sprintf(
					'Cannot add self as child for instance of class %s',
					$class
				),
				'1.0.0',
				FunctionalityInitializationFailure::class,
				LogLevel::ERROR,
				'framework'
			);
		}

		$child->set_parent( $this );
		$this->children[ $child->get_instance_id() ] = $child;

		return null;
	}

	/**
	 * Handles initialization of a component by returning a simple null if everything worked out, or an exception if not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Functionality   $functionality  The instance of the functionality that needs to be initialized.
	 *
	 * @return  FunctionalityInitializationFailure|\Exception|null
	 */
	protected function try_initialization( Functionality $functionality ): ?FunctionalityInitializationFailure {
		try {
			$result = $functionality->initialize();
			if ( ! is_null( $result ) ) {
				$result = $this->get_logging_service()->log_event_and_return_exception(
					LogLevel::ERROR,
					FunctionalityInitializationFailure::class,
					vsprintf(
						'Failed to initialize functionality %1$s for parent %2$s. Error: %3$s',
						array( $functionality::get_full_class_name(), static::get_full_class_name(), $result->getMessage() )
					),
					'framework'
				);
			}
		} catch ( \Exception $e ) {
			$result = $this->get_logging_service()->log_event_and_return_exception(
				LogLevel::ERROR,
				FunctionalityInitializationFailure::class,
				$e->getMessage(),
				'framework'
			);
		}

		return $result;
	}

	// endregion
}
