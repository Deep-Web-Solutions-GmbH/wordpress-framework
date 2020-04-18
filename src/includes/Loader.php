<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility layer between the framework and WordPress' API for filters, actions, and shortcodes.
 *
 * Maintain a list of all hooks that are registered throughout the plugin, and register them with
 * the WordPress API after calling the run function.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0
 */
final class Loader {
	// region FIELDS

	/**
	 * The actions registered with WordPress to fire when the plugin loads.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array   $actions
	 */
	private array $actions = array();

	/**
	 * The filters registered with WordPress to fire when the plugin loads.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array   $filters
	 */
	private array $filters = array();

	/**
	 * The shortcodes registered with WordPress that can be used after the plugin loads.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array   $shortcodes
	 */
	private array $shortcodes = array();

	// endregion

	// region MAGIC METHODS

	/**
	 * Loader constructor. Even if there are multiple versions of the loader going around, make sure that we always
	 * have access to all of them. This is important such that one plugin can potentially remove hooks and shortcodes
	 * registered by another.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		$GLOBALS['dws_framework_core_loaders']   = $GLOBALS['dws_framework_core_loaders'] ?? array();
		$GLOBALS['dws_framework_core_loaders'][] = $this;
	}

	// endregion

	// region METHODS

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 *
	 * @param    string         $hook           The name of the WordPress action that is being registered.
	 * @param    object|null    $component      A reference to the instance of the object on which the action is defined.
	 * @param    string         $callback       The name of the function definition on the $component.
	 * @param    int            $priority       Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int            $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Remove an action from the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 *
	 * @param    string         $hook           The name of the WordPress action that is being deregistered.
	 * @param    object         $component      A reference to the instance of the object on which the action is defined.
	 * @param    string|null    $callback       The name of the function definition on the $component.
	 * @param    int            $priority       Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int            $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function remove_action( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->actions = $this->remove( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
		foreach ( $GLOBALS['dws_framework_core_loaders'] as $loader ) {
			if ( $loader !== $this ) {
				$loader->remove_action( $hook, $component, $callback, $priority, $accepted_args );
			}
		}
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $hook           The name of the WordPress filter that is being registered.
	 * @param   object|null     $component      A reference to the instance of the object on which the filter is defined.
	 * @param   string          $callback       The name of the function definition on the $component.
	 * @param   int             $priority       Optional. he priority at which the function should be fired. Default is 10.
	 * @param   int             $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_filter( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Remove a filter from the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 *
	 * @param    string         $hook           The name of the WordPress filter that is being deregistered.
	 * @param    object|null    $component      A reference to the instance of the object on which the filter is defined.
	 * @param    string         $callback       The name of the function definition on the $component.
	 * @param    int            $priority       Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int            $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function remove_filter( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->filters = $this->remove( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
		foreach ( $GLOBALS['dws_framework_core_loaders'] as $loader ) {
			if ( $loader !== $this ) {
				$loader->remove_filter( $hook, $component, $callback, $priority, $accepted_args );
			}
		}
	}

	/**
	 * Add a new shortcode to the collection to be registered with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $tag            The name of the WordPress shortcode that is being registered.
	 * @param   object|null     $component      A reference to the instance of the object on which the shortcode is defined.
	 * @param   string          $callback       The name of the function definition on the $component.
	 */
	public function add_shortcode( string $tag, $component, string $callback ) : void {
		$this->shortcodes = $this->add( $this->shortcodes, $tag, $component, $callback, -1, -1 );
	}

	/**
	 * Remove a shortcode from the collection to be registered with WordPress.
	 *
	 * @param   string          $tag            The name of the WordPress shortcode that is being deregistered.
	 * @param   object|null     $component      A reference to the instance of the object on which the shortcode is defined.
	 * @param   string          $callback       The name of the function definition on the $component.
	 */
	public function remove_shortcode( string $tag, $component, string $callback ) : void {
		$this->shortcodes = $this->remove( $this->shortcodes, $tag, $component, $callback, -1, -1 );
		foreach ( $GLOBALS['dws_framework_core_loaders'] as $loader ) {
			if ( $loader !== $this ) {
				$loader->remove_shortcode( $tag, $component, $callback );
			}
		}
	}

	/**
	 * Register the filters, actions, and shortcodes with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			if ( empty( $hook['component'] ) ) {
				add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
			} else {
				add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}
		}

		foreach ( $this->actions as $hook ) {
			if ( empty( $hook['component'] ) ) {
				add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
			} else {
				add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}
		}

		foreach ( $this->shortcodes as $hook ) {
			if ( empty( $hook['component'] ) ) {
				add_shortcode( $hook['hook'], $hook['callback'] );
			} else {
				add_shortcode( $hook['hook'], array( $hook['component'], $hook['callback'] ) );
			}
		}
	}

	// endregion

	// region HELPERS

	/**
	 * A utility function that is used to register the actions and hooks into a single collection.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 *
	 * @access   private
	 *
	 * @param    array          $hooks          The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string         $hook           The name of the WordPress filter that is being registered.
	 * @param    object|null    $component      A reference to the instance of the object on which the filter is defined.
	 * @param    string         $callback       The name of the function definition on the $component.
	 * @param    int            $priority       The priority at which the function should be fired.
	 * @param    int            $accepted_args  The number of arguments that should be passed to the $callback.
	 *
	 * @return   array      The collection of actions and filters registered with WordPress.
	 */
	private function add( array $hooks, string $hook, $component, string $callback, int $priority, int $accepted_args ) : array {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * A utility function that is used to remove the actions and hooks from the single collection.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 *
	 * @access   private
	 *
	 * @param    array          $hooks          The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string         $hook           The name of the WordPress filter that is being registered.
	 * @param    object|null    $component      A reference to the instance of the object on which the filter is defined.
	 * @param    string         $callback       The name of the function definition on the $component.
	 * @param    int            $priority       The priority at which the function should be fired.
	 * @param    int            $accepted_args  The number of arguments that should be passed to the $callback.
	 *
	 * @return   array      The collection of actions and filters registered with WordPress.
	 */
	private function remove( array $hooks, string $hook, $component, string $callback, int $priority, int $accepted_args ) : array {
		foreach ( $hooks as $index => $hook_info ) {
			if ( $hook_info['hook'] === $hook && $hook_info['component'] === $component && $hook_info['callback'] === $callback && $hook_info['priority'] === $priority && $hook_info['accepted_args'] === $accepted_args ) {
				unset( $hooks[ $index ] );
				break;
			}
		}

		return $hooks;
	}

	// endregion
}
