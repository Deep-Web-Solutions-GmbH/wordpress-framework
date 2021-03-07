<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable\InitializableTrait;
use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\SetupableTrait;
use DeepWebSolutions\Framework\Core\Actions\Initializable\SetupOnInitializationTrait;
use DeepWebSolutions\Framework\Core\Actions\Setupable\RunOnSetupTrait;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\InitializableInterface;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;
use DeepWebSolutions\Framework\Foundations\Hierarchy\ChildInterface;
use DeepWebSolutions\Framework\Utilities\DependencyInjection\ContainerAwareInterface;
use DeepWebSolutions\Framework\Utilities\DependencyInjection\ContainerAwareTrait;
use DeepWebSolutions\Framework\Utilities\PluginComponent\AbstractActiveablePluginNode;
use Exception;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the piping required for auto-magical lifecycle execution of a plugin node.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPluginFunctionality extends AbstractActiveablePluginNode implements ContainerAwareInterface, InitializableInterface, SetupableInterface {
	// region TRAITS

	use ContainerAwareTrait;
	use InitializableTrait;
	use SetupableTrait;
	use SetupOnInitializationTrait;
	use RunOnSetupTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the plugin instance that the current node belongs to.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @throws  LogicException      Thrown if the node does NOT belong to a plugin tree.
	 *
	 * @return  AbstractPluginRoot
	 */
	public function get_plugin(): AbstractPluginRoot {
		$plugin = $this->get_closest( AbstractPluginRoot::class );
		if ( $plugin instanceof AbstractPluginRoot ) {
			return $plugin;
		}

		/* @noinspection PhpUnhandledExceptionInspection */
		throw $this->log_event_and_return_exception(
			LogLevel::ERROR,
			sprintf(
				'Could not find plugin root from within node. Node name: %s',
				$this->get_instance_name()
			),
			LogicException::class,
			null,
			'framework'
		);
	}

	/**
	 * Gets an instance of a dependency injection container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  ContainerInterface
	 */
	public function get_container(): ContainerInterface {
		return $this->get_plugin()->get_container();
	}

	/**
	 * Sets a container on the instance.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   ContainerInterface|null     $container      NOT USED BY THIS IMPLEMENTATION.
	 */
	public function set_container( ?ContainerInterface $container = null ): void {
		$this->di_container = $this->get_container();
	}

	/**
	 * The starting point of instance logic. Loads and initializes children functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     InitializableTrait::initialize()
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	public function initialize(): ?FunctionalityInitFailureException {
		if ( is_null( $this->is_initialized ) ) {
			// Pre-initialization actions.
			$this->set_plugin();
			$this->set_container();

			// Foundations initialization.
			$result = $this->initialize_foundations();
			if ( ! is_null( $result ) ) {
				$this->is_initialized        = false;
				$this->initialization_result = new FunctionalityInitFailureException(
					$result->getMessage(),
					$result->getCode(),
					$result
				);

				return $this->initialization_result;
			}

			// Add DI container children.
			if ( ! is_null( $result = $this->add_children() ) ) { // phpcs:ignore
				$this->is_initialized        = false;
				$this->initialization_result = $result;
				return $this->initialization_result;
			}

			// Initialize children first.
			foreach ( $this->get_children() as $child ) {
				if ( ! is_null( $result = $this->try_child_initialization( $child ) ) ) { // phpcs:ignore
					$this->is_initialized        = false;
					$this->initialization_result = $result;
					return $this->initialization_result;
				}
			}

			// Sub-tree initialization successful.
			$this->is_initialized        = true;
			$this->initialization_result = null;

			if ( ! is_null( $result = $this->maybe_initialize_integrations() ) ) { // phpcs:ignore
				$this->is_initialized        = false;
				$this->initialization_result = new FunctionalityInitFailureException(
					$result->getMessage(),
					$result->getCode(),
					$result
				);
			}
		}

		return $this->initialization_result;
	}

	/**
	 * Adds a child to the list of children of the current instance. If a string is passed along, the DI container
	 * is used to resolve the entry.
	 *
	 * @param   object|string   $child      Object to add or string to resolve before adding.
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	public function add_child( $child ): ?FunctionalityInitFailureException {
		$child = is_string( $child ) ? $this->get_container_entry( $child ) : $child;
		if ( is_null( $child ) || ! is_a( $child, ChildInterface::class ) || $child->has_parent() || $child === $this ) {
			return $this->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				sprintf(
					'Invalid child! Cannot add instance of type %1$s as child to instance of type %2$s.',
					get_class( $child ),
					static::get_full_class_name()
				),
				'1.0.0',
				FunctionalityInitFailureException::class,
				null,
				LogLevel::ERROR,
				'framework'
			);
		}

		$child->set_parent( $this );
		$this->children[] = $child;

		return null;
	}

	// endregion

	// region HELPERS

	/**
	 * Add the children returned by the 'get_di_container_children' method as children of the instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	protected function add_children(): ?FunctionalityInitFailureException {
		foreach ( $this->get_di_container_children() as $child ) {
			$result = $this->add_child( $child );
			if ( ! is_null( $result ) ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Attempts to run the initialization routine of a child node.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ChildInterface  $child  Child object to try and initialize.
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	protected function try_child_initialization( ChildInterface $child ): ?FunctionalityInitFailureException {
		$result = null;

		if ( $child instanceof InitializableInterface ) {
			try {
				$result = $child->initialize();
			} catch ( Exception $exception ) {
				$result = $exception;
			}

			if ( ! is_null( $result ) ) {
				$result = $this->log_event_and_return_exception(
					LogLevel::ERROR,
					vsprintf(
						'Failed to initialize child %1$s for parent %2$s. Error type: %3$s. Error message: %4$s',
						array( get_class( $child ), static::get_full_class_name(), get_class( $result ), $result->getMessage() )
					),
					FunctionalityInitFailureException::class,
					$result,
					'framework'
				);
			}
		}

		return $result;
	}

	/**
	 * Inheriting classes should return their DI children here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_di_container_children(): array {
		return array();
	}

	// endregion
}
