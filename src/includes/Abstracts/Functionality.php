<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\FunctionalityInitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * A template for encapsulating all the piping required for a DWS functionality.
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
	private int $functionality_depth = -1;

	/**
	 * The human-readable description of the current functionality. Optional.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string|null
	 */
	private ?string $description = null;

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

	// endregion

	// region MAGIC METHODS

	/**
	 * Functionality destructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __destruct() {
		$this->parent = null;
		$this->plugin = null;
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
			while ( ! is_null( $current->has_parent() ) ) {
				$current = $current->get_parent();
			}

			$this->plugin = $current;
		}

		return $this->plugin;
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
		return empty( $this->children );
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
		$this->parent = $parent;
	}

	// endregion

	// region METHODS

	/**
	 * Functionalities can define their own prerequisites to avoid initialization.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function are_prerequisites_fulfilled(): bool {
		return true;
	}

	/**
	 * The starting point of instance logic. Loads and initializes children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  FunctionalityInitializationFailure|\Exception   If a child functionality fails to initialize, an exception will be thrown.
	 *
	 * @return  bool
	 */
	public function initialize(): bool {
		// If the prerequisites are not fulfilled, don't initialize.
		if ( ! $this->are_prerequisites_fulfilled() ) {
			return false;
		}

		// If the functionality's own initialization fails, stop.
		if ( ! $this->local_initialize() ) {
			return false;
		}

		$this->set_loaded_functionality_fields_as_children();
		if ( ! $this->load_children_functionalities() ) {
			return false;
		}

		$children = $this->get_children();
		foreach ( $children as $child ) {
			$result = $this->try_initialization( $child );
			if ( ! is_null( $result ) ) {
				throw $result;
			}
		}

		$this->initialized = true;
		$this->setup();

		return true;
	}

	/**
	 * Children classes should overwrite this function to initialize their own non-functionality fields. Gets called
	 * right after the prerequisites have been confirmed but before loading any children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	protected function local_initialize(): bool {
		return true;
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
	 * @return  bool
	 */
	protected function load_children_functionalities(): bool {
		return true;
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
	 * @return  Functionality|null
	 */
	public function add_child( string $class ): ?Functionality {
		try {
			$child = $this->plugin->get_container()->get( $class );
		} catch ( \Exception $e ) {
			$this->logger->error(
				sprintf(
					/* translators: 1: Class to be instantiated, 2: Error type thrown, 3: Error message thrown. */
					__( 'Failed to instantiate class %1$s. Error type: %2$s. Error message: %3$s', 'dws-wp-framework-core' ),
					esc_html( $class ),
					$e->getMessage()
				)
			);
			return null;
		}

		if ( ! ( $child instanceof Functionality ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: Name of child class, 2: Name of parent class */
					esc_html__( 'Children of functionalities must be functionalities too! Cannot add instance of type %1$s as child to instance of type %2$s.', 'dws-wp-framework-core' ),
					esc_html( $class ),
					static::class // phpcs:ignore
				),
				'1.0.0'
			);
			return null;
		}

		if ( ! is_null( $child->get_parent() ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: Name of child class, 2: Name of parent class */
					esc_html__( 'Child instance %1$s already has a parent. Cannot set parent as %2$s.', 'dws-wp-framework-core' ),
					esc_html( $class ),
                    static::class // phpcs:ignore
				),
				'1.0.0'
			);
			return null;
		}

		if ( $child === $this ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: Class name */
					esc_html__( 'Cannot add self as child for instance of class %s', 'dws-wp-framework-core' ),
					esc_html( $class )
				),
				'1.0.0'
			);
			return null;
		}

		$child->set_parent( $this );
		$this->children[] = $child;

		return $child;
	}

	/**
	 * Checks whether the current functionality is initialized, and also all of its ancestors.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_active(): bool {
		$current = $this;

		while ( $current->has_parent() ) {
			if ( ! $current->is_initialized() ) {
				return false;
			}

			$current = $current->has_parent();
		};

		return $current->is_initialized();
	}

	/**
	 * Execute the code of the functionality.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final protected function setup(): void {
		// Only run functionality code if everything is initialized.
		if ( ! $this->is_active() ) {
			return;
		}

		// Execute the setup logic of functionality traits.
		foreach ( class_uses( $this ) as $used_trait ) {
			if ( array_search( 'DeepWebSolutions\Framework\Core\Traits\Abstracts\FunctionalityTrait', class_uses( $used_trait ), true ) !== false ) {
				$trait_components = explode( '\\', $used_trait );
				$method_name      = 'setup_' . strtolower( end( $trait_components ) );

				if ( method_exists( $this, $method_name ) ) {
					$this->{$method_name}();
				}
			}
		}
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
	 * @throws  \ReflectionException    This should be impossible to happen, but it's good to be aware of it.
	 *
	 * @return  bool
	 */
	private function set_loaded_functionality_fields_as_children(): bool {
		$fields = ( new \ReflectionClass( $this ) )->getProperties();
		foreach ( $fields as $field ) {
			if ( $field->getType()->getName() === self::class && $field->isInitialized( $this ) ) {
				$functionality = $this->{$field->getName()};

				if ( $functionality->has_parent() ) {
					$this->logger->warning(
						sprintf(
							/* translators: 1: The child functionality, 2: The parent functionality. */
							esc_html__( 'Functionality %1$s already has a set parent. Overwriting with parent %2$s.', 'dws-wp-framework-core' ),
							$functionality->get_root_public_name(),
							$this->get_root_public_name()
						)
					);
				}

				$functionality->set_parent( $this );
			}
		}

		return true;
	}

	/**
	 * Handles initialization of a component by returning a simple null if everything worked out, or an exception if not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Functionality   $functionality  The instance of the functionality that needs to be initialized.
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function try_initialization( Functionality $functionality ): ?FunctionalityInitializationFailure {
		$result = null;

		try {
			if ( ! $functionality->initialize() ) {
				/* translators: 1: Child functionality, 2: Parent functionality. */
				$message = esc_html__( 'Failed to initialize functionality %1$s for parent %2$s', 'dws-wp-framework-core' );
				$args    = array( $functionality::get_full_class_name(), static::get_full_class_name() );
				$result  = new FunctionalityInitializationFailure( vsprintf( $message, $args ) );
			}
		} catch ( \Exception $e ) {
			$result = $e;
		}

		return $result;
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

	// endregion
}
