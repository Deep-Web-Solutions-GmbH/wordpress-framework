<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Utilities\Services\DependenciesCheckerService;
use DeepWebSolutions\Framework\Utilities\Services\LoggingService;
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
abstract class Functionality extends Root {
	// region FIELDS AND CONSTANTS

	/**
	 * Whether the current functionality has been successfully initialized or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected bool $initialized = false;

	/**
	 * Whether the current functionality is active or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool|null
	 */
	protected ?bool $active = null;

	/**
	 * The instance of the main plugin class to which this functionality "belongs".
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     PluginBase|null
	 */
	private ?PluginBase $plugin = null;

	/**
	 * The parent of this functionality, if it exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     Functionality|null
	 */
	private ?Functionality $parent = null;

	/**
	 * The children of this functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     Functionality[]
	 */
	private array $children = array();

	/**
	 * The recursive depth of the current functionality. The main plugin instance is 0.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     int
	 */
	private int $functionality_depth = 0;

	/**
	 * The human-readable description of the current functionality. Optional.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string|null
	 */
	private ?string $description;

	// endregion

	// region MAGIC METHODS

	/**
	 * Functionality constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   LoggingService  $logging_service    Instance of logging service used throughout the plugin.
	 * @param   string|null     $root_id            The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|null     $root_name          The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 * @param   string|null     $description        The human-readable description of the current functionality.
	 */
	public function __construct( LoggingService $logging_service, ?string $root_id = null, ?string $root_name = null, ?string $description = null ) {
		parent::__construct( $logging_service, $root_id, $root_name );
		$this->description = $description;
	}

	// endregion

	// region GETTERS

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
	 * Returns the instance of the main plugin class to which the current instance "belongs".
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  PluginBase|null
	 */
	public function get_plugin(): ?PluginBase {
		if ( null === $this->plugin ) {
			$current = $this;
			while ( $current->has_parent() ) {
				$current = $current->get_parent();
			}

			if ( $current instanceof PluginBase ) {
				$this->plugin = $current;
			} else {
				$this->logging_service->log_event(
					LogLevel::ERROR,
					sprintf(
						'Found functionality without parent inside plugin tree. Functionality name: %s',
						$current->get_root_public_name()
					),
					'framework'
				);
			}
		}

		return $this->plugin;
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

	/**
	 * Returns the human-readable description of the current functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string|null
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Returns the translated version of the functionality's public name.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_translated_public_name(): string {
		return __( $this->get_root_public_name(), $this->get_plugin()->get_plugin_language_domain() ); // phpcs:ignore
	}

	/**
	 * Returns the translated version of the human-readable description of the current functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string|null
	 */
	public function get_translated_description(): ?string {
		return is_null( $this->get_description() )
			? null
			: __( $this->get_description(), $this->get_plugin()->get_plugin_language_domain() ); // phpcs:ignore
	}

	/**
	 * Returns whether the current functionality is initialized or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_initialized(): bool {
		return $this->initialized;
	}

	/**
	 * Checks whether the current functionality is active, and also all of its ancestors.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_active(): bool {
		if ( ! is_null( $this->active ) ) {
			return $this->active; // Status memoization.
		}

		// Start by assuming the functionality is active.
		$this->active = true;

		// Check if the functionality is optional, and if yes, whether it should be active or not.
		$optional_active = true;
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( array_search( 'DeepWebSolutions\Framework\Core\Traits\Abstracts\Optional', class_uses( $used_trait ), true ) !== false ) {
				$trait_components = explode( '\\', $used_trait );
				$method_name      = 'is_active_' . strtolower( end( $trait_components ) );

				if ( method_exists( $this, $method_name ) ) {
					$optional_active = $this->{$method_name}();

					if ( ! $optional_active ) {
						// Functionality is optional and currently disabled.
						$this->active = false;
						break;
					}
				}
			}
		}

		// If the functionality is (either mandatory or optionally active), check ancestors and any dependencies.
		if ( $optional_active ) {
			// If parent exists, make sure it's also active.
			if ( $this->has_parent() ) {
				if ( ! $this->get_parent()->is_active() ) {
					$this->active = false;
				}
			}

			// If ancestors are all active, check local dependencies.
			if ( $this->active && in_array( 'DeepWebSolutions\Framework\Core\Traits\Dependencies', class_uses( $this ), true ) ) {
				/** @noinspection PhpUndefinedMethodInspection */ // phpcs:ignore
				/** @var DependenciesCheckerService $dependencies_checker */ // phpcs:ignore
				$dependencies_checker = $this->get_dependencies_checker();
				$this->active         = $dependencies_checker->are_dependencies_fulfilled();
			}
		}

		return $this->active;
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

	// endregion

	// region METHODS

	/**
	 * The starting point of instance logic. Loads and initializes children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	public function initialize(): ?FunctionalityInitializationFailure {
		// Prevent multiple initialization.
		if ( true === $this->initialized ) {
			$this->logging_service->log_event( LogLevel::WARNING, sprintf( 'Attempt to re-initialize %s. Functionality has already been initialized.', $this::get_full_class_name() ), 'framework' );
			return null;
		}

		// If the class' own initialization fails, stop.
		if ( ! is_null( $result = $this->initialize_local() ) ) { // phpcs:ignore
			return $result;
		}

		// Instantiate all children and properly set parent-child relations.
		if ( ! is_null( $result = $this->set_loaded_functionality_fields_as_children() ) ) { // phpcs:ignore
			return $result;
		}
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
		$this->setup();

		return null;
	}

	/**
	 * Children classes should overwrite this function to initialize their own non-functionality fields. Gets called
	 * right after the prerequisites have been confirmed but before loading any children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function initialize_local(): ?FunctionalityInitializationFailure {
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

	// endregion

	// region HELPERS

	/**
	 * It's possible that functionalities are loaded through the constructor through PHP-DI's autowiring functionality.
	 * If that's the case, they're probably stored in fields of the class instance. This function goes through the fields
	 * and adds the Functionalities as children.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	final protected function set_loaded_functionality_fields_as_children(): ?FunctionalityInitializationFailure {
		$fields = ( new \ReflectionClass( $this ) )->getProperties();
		foreach ( $fields as $field ) {
			$functionality = $this->{$field->getName()};
			if ( $functionality instanceof Functionality ) {
				if ( $functionality->has_parent() ) {
					if ( $functionality->get_parent() !== $this ) {
						return $this->logging_service->log_event_and_return_exception(
							LogLevel::ERROR,
							FunctionalityInitializationFailure::class,
							sprintf(
								'Functionality %1$s already has a set parent %2$s. Cannot overwrite with parent %3$s.',
								$functionality->get_root_public_name(),
								$functionality->get_parent()->get_root_public_name(),
								$this->get_root_public_name()
							),
							'framework'
						);
					}
				}

				if ( ! is_null( $result = $this->add_child( $functionality::get_full_class_name() ) ) ) { // phpcs:ignore
					return $result;
				}
			}
		}

		return null;
	}

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
	final protected function add_child( string $class ): ?FunctionalityInitializationFailure {
		try {
			$child = $this->get_plugin()->get_container()->get( $class );
		} catch ( \Exception $e ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return $this->logging_service->log_event_and_return_exception(
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
			return $this->logging_service->log_event_and_doing_it_wrong_and_return_exception(
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
			return $this->logging_service->log_event_and_doing_it_wrong_and_return_exception(
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
			return $this->logging_service->log_event_and_doing_it_wrong_and_return_exception(
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
		$this->children[ $child->get_root_id() ] = $child;

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
	final protected function try_initialization( Functionality $functionality ): ?FunctionalityInitializationFailure {
		try {
			$result = $functionality->initialize();
			if ( ! is_null( $result ) ) {
				$result = $this->logging_service->log_event_and_return_exception(
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
			$result = $this->logging_service->log_event_and_return_exception(
				LogLevel::ERROR,
				FunctionalityInitializationFailure::class,
				$e->getMessage(),
				'framework'
			);
		}

		return $result;
	}

	/**
	 * Execute the setup logic of functionality traits.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final protected function setup(): void {
		// Only run functionality code if class is active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Execute the setup logic of functionality traits.
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( array_search( 'DeepWebSolutions\Framework\Core\Traits\Abstracts\Setup', class_uses( $used_trait ), true ) !== false ) {
				$trait_components = explode( '\\', $used_trait );
				$method_name      = 'setup_' . strtolower( end( $trait_components ) );

				if ( method_exists( $this, $method_name ) ) {
					$this->get_plugin()->get_container()->call( array( $this, $method_name ) );
				}
			}
		}
	}

	// endregion
}
