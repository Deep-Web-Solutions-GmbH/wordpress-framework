<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Resources;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define a logic for handling parent-child relations.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Resources
 */
interface Hierarchable {
	// region GETTERS

	/**
	 * Method for determining whether the node is a root or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool    False if it's a root, true otherwise.
	 */
	public function has_parent(): bool;

	/**
	 * Method for retrieving the instance's parent. Should return null if the node is the root.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Hierarchable|null
	 */
	public function get_parent(): ?Hierarchable;

	/**
	 * Method for determining whether the node is a leaf or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool    False if it's a leaf, true otherwise.
	 */
	public function has_children(): bool;

	/**
	 * Method for retrieving the node's children.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	public function get_children(): array;

	/**
	 * Method for retrieving the node's depth within the tree.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  int
	 */
	public function get_depth(): int;

	// endregion

	// region SETTERS

	/**
	 * Method for setting the parent of the node.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Hierarchable    $parent     The value to set the parent to.
	 */
	public function set_parent( Hierarchable $parent ): void;

	/**
	 * Method for setting the depth of the node.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $depth      The value to set the depth to.
	 */
	public function set_depth( int $depth ): void;

	// endregion

	// region METHODS

	/**
	 * Method for adding a new child to the instance.
	 *
	 * @param   string|Hierarchable     $child      Object or class name to instantiate and add to the hierarchy.
	 *
	 * @return  mixed
	 */
	public function add_child( $child );

	// endregion
}
