<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Abstracts\Exceptions\Initialization\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Initializable;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Setupable;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable\Initialize;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Setup;
use DeepWebSolutions\Framework\Core\Traits\Integrations\RunOnSetup;
use DeepWebSolutions\Framework\Core\Traits\Integrations\SetupOnInitialize;
use Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the piping required for auto-magical lifecycle execution of a plugin node.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
 *
 * @see     PluginNode
 */
abstract class PluginFunctionality extends PluginNode implements Initializable, Setupable {
	// region TRAITS

	use Initialize;
	use SetupOnInitialize;

	use Setup;
	use RunOnSetup;

	// endregion

	// region INHERITED METHODS

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
		if ( is_null( $this->is_initialized ) ) {
			$this->set_plugin();

			// Perform any local initialization, if applicable.
			if ( ! is_null( $result = $this->maybe_initialize_traits() ) ) { // phpcs:ignore
				$this->is_initialized = false;
				return new FunctionalityInitializationFailure( $result->getMessage() );
			}
			if ( ! is_null( $result = $this->maybe_initialize_local() ) ) { // phpcs:ignore
				$this->is_initialized = false;
				return new FunctionalityInitializationFailure( $result->getMessage() );
			}

			// Instantiate all children and properly set parent-child relations.
			if ( ! is_null( $result = $this->register_children() ) ) { // phpcs:ignore
				$this->is_initialized = false;
				return $result;
			}

			// Initialize the children as well.
			foreach ( $this->get_children() as $child ) {
				if ( $child instanceof PluginNode && ! is_null( $result = $this->try_initialization( $child ) ) ) { // phpcs:ignore
					$this->is_initialized = false;
					return $result;
				}
			}

			// Sub-tree initialization successful.
			$this->is_initialized = true;

			if ( ! is_null( $result = $this->maybe_initialize_integrations() ) ) { // phpcs:ignore
				return new FunctionalityInitializationFailure(
					$result->getMessage(),
					$result->getCode(),
					$result
				);
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
	 * @see     PluginNode::add_child()
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	public function add_child( $class ): ?FunctionalityInitializationFailure {
		if ( ! is_a( $class, PluginNode::class, true ) ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->get_logging_service()->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				sprintf(
					'Children of functionalities must be plugin nodes too! Cannot add instance of type %1$s as child to instance of type %2$s.',
					$class,
					static::class
				),
				'1.0.0',
				FunctionalityInitializationFailure::class,
				LogLevel::ERROR,
				'framework'
			);
		}

		try {
			$child = $this->get_plugin()->get_container()->get( $class );
		} catch ( \Exception $e ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
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

		if ( $child->has_parent() ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
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

	// endregion

	// region METHODS

	/**
	 * Child classes should define their children functionalities in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function define_children(): array {
		return array();
	}

	// endregion

	// region HELPERS

	/**
	 * Children classes should use this function to lazy-load their children functionalities. Possibly conditionally.
	 * MUST use the function 'add_child' for everything to work properly.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     PluginFunctionality::add_child()
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function register_children(): ?FunctionalityInitializationFailure {
		$children = $this->define_children();
		foreach ( $children as $child ) {
			$result = $this->add_child( $child );
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Handles initialization of a node by returning a simple null if everything worked out, or an exception if not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   PluginNode      $node    The instance of the node that needs to be initialized.
	 *
	 * @return  FunctionalityInitializationFailure|\Exception|null
	 */
	protected function try_initialization( PluginNode $node ): ?FunctionalityInitializationFailure {
		$result = null;

		if ( $node instanceof Initializable ) {
			try {
				$result = $node->initialize();
				if ( ! is_null( $result ) ) {
					$result = $this->get_logging_service()->log_event_and_return_exception(
						LogLevel::ERROR,
						FunctionalityInitializationFailure::class,
						vsprintf(
							'Failed to initialize functionality %1$s for parent %2$s. Error: %3$s',
							array( $node::get_full_class_name(), static::get_full_class_name(), $result->getMessage() )
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
		}

		return $result;
	}

	// endregion
}
