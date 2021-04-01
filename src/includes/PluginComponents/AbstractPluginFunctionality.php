<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable\InitializableTrait;
use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\SetupableTrait;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializeLocalTrait;
use DeepWebSolutions\Framework\Foundations\Actions\InitializableInterface;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\InitializeChildren;
use DeepWebSolutions\Framework\Foundations\Hierarchy\ChildInterface;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Plugin\AbstractPluginNode;
use DeepWebSolutions\Framework\Foundations\Hierarchy\States\ActiveParent;
use DeepWebSolutions\Framework\Foundations\Hierarchy\States\DisabledParent;
use DeepWebSolutions\Framework\Foundations\States\ActiveableInterface;
use DeepWebSolutions\Framework\Foundations\States\DisableableInterface;
use DeepWebSolutions\Framework\Foundations\Utilities\DependencyInjection\ContainerAwareInterface;
use DeepWebSolutions\Framework\Foundations\Utilities\DependencyInjection\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

\defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the piping required for auto-magical lifecycle execution of a plugin node.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPluginFunctionality extends AbstractPluginNode implements ContainerAwareInterface, ActiveableInterface, DisableableInterface, InitializableInterface, SetupableInterface {
	// region TRAITS

	use ActiveParent;
	use ContainerAwareTrait;
	use DisabledParent;
	use InitializableTrait;
	use InitializeLocalTrait;
	use InitializeChildren;
	use SetupableTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the plugin instance that the current node belongs to.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  AbstractPluginRoot
	 */
	public function get_plugin(): AbstractPluginRoot { // phpcs:ignore
		/* @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::get_plugin();
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
		if ( ! \is_null( $container ) ) {
			$this->log_event( 'The DI container can not be set directly on a functionality', array(), 'framework' )
					->set_log_level( LogLevel::ERROR )
					->doing_it_wrong( __FUNCTION__, '1.0.0' )
					->finalize();
			return;
		}

		$this->di_container = $this->get_container();
	}

	/**
	 * Instantiates and adds DI children to the instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailureException|null
	 */
	public function initialize_local(): ?InitializationFailureException {
		// Pre-initialization actions.
		$this->set_plugin();
		$this->set_container();

		// Add DI container children.
		return $this->add_children();
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
		$child = \is_string( $child ) ? $this->get_container_entry( $child ) : $child;
		if ( \is_null( $child ) || ! \is_a( $child, ChildInterface::class ) || $child->has_parent() || $child === $this ) {
			return $this->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				\sprintf(
					'Invalid child! Cannot add instance of type %1$s as child to instance of type %2$s.',
					\is_null( $child ) ? null : \get_class( $child ),
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
			if ( ! \is_null( $result ) ) {
				return $result;
			}
		}

		return null;
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
