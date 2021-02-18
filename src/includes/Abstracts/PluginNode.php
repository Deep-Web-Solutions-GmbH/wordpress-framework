<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Interfaces\Resources\Containerable;
use DeepWebSolutions\Framework\Core\Interfaces\Resources\Hierarchable;
use DeepWebSolutions\Framework\Core\Interfaces\Resources\Traits\Hierarchy;
use DeepWebSolutions\Framework\Utilities\Abstracts\Base;
use DeepWebSolutions\Framework\Utilities\Interfaces\Resources\Pluginable;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\IsActiveable;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\IsDisableable;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\Traits\IsActiveable\Active;
use DeepWebSolutions\Framework\Utilities\Interfaces\States\Traits\IsDisableable\Disable;
use DeepWebSolutions\Framework\Utilities\Services\LoggingService;
use DI\Container;
use Exception;
use Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the logic of a hierarchical tree-like class structure.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
 */
abstract class PluginNode extends Base implements Containerable, Hierarchable, IsActiveable, IsDisableable {
	// region TRAITS

	use Hierarchy;
	use Active { is_active as is_active_trait; }
	use Disable { is_disabled as is_disabled_trait; }

	// endregion

	// region MAGIC METHODS

	/**
	 * Node constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   LoggingService  $logging_service    Instance of the plugin's logging service.
	 * @param   string|null     $node_id            The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|null     $node_name          The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 */
	public function __construct( LoggingService $logging_service, ?string $node_id = null, ?string $node_name = null ) {
		parent::__construct( $node_id, $node_name );
		$this->set_logging_service( $logging_service );
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * Gets the static instance of the PHP-DI container used throughout the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Containerable::get_container()
	 *
	 * @throws  Exception  Thrown if the node does NOT belong to a plugin tree.
	 *
	 * @return  Container
	 */
	public function get_container(): Container {
		return $this->get_plugin()->get_container();
	}

	/**
	 * Sets the plugin instance that the current node belongs to.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  Exception  Thrown if the node does NOT belong to a plugin tree.
	 *
	 * @see     Identity::get_plugin()
	 */
	public function get_plugin(): Pluginable {
		$current = $this;
		while ( $current->has_parent() ) {
			$current = $current->get_parent();
		}

		if ( $current instanceof PluginRoot ) {
			return $current;
		}

		throw $this->get_logging_service()->log_event_and_return_exception(
			LogLevel::ERROR,
			sprintf(
				'Found node without parent inside plugin tree. Node name: %s',
				$current->get_instance_public_name()
			),
			Exception::class,
			null,
			'framework'
		);
	}

	/**
	 * Checks whether the current functionality is active, and also all of its ancestors.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     IsActiveable::is_active()
	 * @see     Active::is_active()
	 *
	 * @return  bool
	 */
	public function is_active(): bool {
		if ( is_null( $this->is_active ) ) {
			$this->is_active = ( $this->has_parent() && ! $this->get_parent()->is_active() )
				? false
				: $this->is_active_trait();
		}

		return $this->is_active;
	}

	/**
	 * Checks whether the current functionality is disabled, and also all of its ancestors.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     IsDisableable::is_disabled()
	 * @see     Disable::is_disabled()
	 *
	 * @return  bool
	 */
	public function is_disabled(): bool {
		if ( is_null( $this->is_disabled ) ) {
			$this->is_disabled = ( $this->has_parent() && $this->get_parent()->is_disabled() )
				? true
				: $this->is_disabled_trait();
		}

		return $this->is_disabled;
	}

	// endregion

	// region GETTERS

	/**
	 * Method inspired by jQuery's 'closest' for getting the first parent node that is an instance of a given class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $class  The name of the class of the searched-for parent node.
	 *
	 * @return  Hierarchable|null
	 */
	public function get_closest( string $class ): ?Hierarchable {
		if ( is_a( $this, $class ) || ! $this->has_parent() ) {
			return null;
		}

		$current = $this;
		do {
			$current = $current->get_parent();
		} while ( $current->has_parent() && ! is_a( $current, $class ) );

		return is_a( $current, $class ) ? $current : null;
	}

	// endregion
}
