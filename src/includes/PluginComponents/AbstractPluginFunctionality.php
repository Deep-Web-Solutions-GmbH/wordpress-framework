<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializeLocalTrait;
use DeepWebSolutions\Framework\Foundations\Actions\InitializableInterface;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\AddContainerChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\InitializeChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\MaybeSetupChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\ParentTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Plugin\AbstractPluginNode;
use DeepWebSolutions\Framework\Foundations\Hierarchy\States\ActiveParentTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\States\DisabledParentTrait;
use DeepWebSolutions\Framework\Foundations\States\ActiveableInterface;
use DeepWebSolutions\Framework\Foundations\States\Disableable\DisableableTrait;
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

	use ActiveParentTrait, DisabledParentTrait;
	use ContainerAwareTrait;
	use InitializeLocalTrait, InitializeChildrenTrait;
	use AddContainerChildrenTrait, ContainerAwareTrait, ParentTrait { // phpcs:ignore
		add_child as protected add_child_trait;
	}
	use MaybeSetupChildrenTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the plugin instance that the current functionality belongs to.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  AbstractPluginFunctionalityRoot
	 */
	public function get_plugin(): AbstractPluginFunctionalityRoot { // phpcs:ignore
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
	 * Automagically sets the plugin and container instances.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailureException|null
	 */
	protected function initialize_local(): ?InitializationFailureException {
		$this->set_plugin();
		$this->set_container();

		return null;
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
		$child  = \is_string( $child ) ? $this->get_container_entry( $child ) : $child;
		$result = $this->add_child_trait( $child );

		if ( true === $result ) {
			return null;
		} else {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->log_event(
				\sprintf(
					'Invalid child! Cannot add instance of type %1$s as child to instance of type %2$s.',
					\is_null( $child ) ? null : \get_class( $child ),
					static::get_full_class_name()
				),
				array(),
				'framework'
			)
						->set_log_level( LogLevel::ERROR )
						->doing_it_wrong( __FUNCTION__, '1.0.0' )
						->return_exception( FunctionalityInitFailureException::class )
						->finalize();
		}
	}

	// endregion
}
