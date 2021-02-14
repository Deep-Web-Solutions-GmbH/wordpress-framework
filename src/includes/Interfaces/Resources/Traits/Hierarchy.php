<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Resources\Traits;

use DeepWebSolutions\Framework\Core\Interfaces\Resources\Hierarchable;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for working with the Hierarchable interface.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Resources\Traits
 */
trait Hierarchy {
	// region FIELDS AND CONSTANTS

	/**
	 * The parent of the using instance, if it exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Hierarchable|null
	 */
	protected ?Hierarchable $parent = null;

	/**
	 * The children of the using instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     Hierarchable[]
	 */
	protected array $children = array();

	/**
	 * The depth of the current instance within the tree. The root instance is 0.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     int
	 */
	protected int $depth = 0;

	// endregion

	// region GETTERS

	/**
	 * Returns whether the using instance has a parent or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function has_parent(): bool {
		return null !== $this->parent;
	}

	/**
	 * Returns the parent of the using instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Hierarchable|null
	 */
	public function get_parent(): ?Hierarchable {
		return $this->parent;
	}

	/**
	 * Returns whether the using instance has any children or not.
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
	 * Returns the children of the using instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Hierarchable[]
	 */
	public function get_children(): array {
		return $this->children;
	}

	/**
	 * Returns the depth of the using instance within the tree.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  int
	 */
	public function get_depth(): int {
		return $this->depth;
	}

	// endregion

	// region SETTERS

	/**
	 * Sets the parent of the using node instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   Hierarchable    $parent     The parent of the using instance.
	 */
	public function set_parent( Hierarchable $parent ): void {
		$this->parent = $parent;
		$this->set_depth( $parent->get_depth() + 1 );
	}

	/**
	 * Sets the depth of the using node instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int     $depth  The depth of the using distance.
	 */
	public function set_depth( int $depth ): void {
		$this->depth = $depth;
	}

	// endregion

	// region METHODS

	/**
	 * Adds a child to the using instance.
	 *
	 * @param   string|object   $class  Instance or class string to instantiate and add as a child.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @return  bool    True if successful, false otherwise.
	 */
	public function add_child( $class ) {
		if ( ! is_a( $this, Hierarchable::class ) ) {
			return false;
		}
		if ( ! is_a( $class, Hierarchable::class, true ) ) {
			return false;
		}

		if ( ! is_object( $class ) ) {
			$class = new $class();
		} else {
			if ( $class->has_parent() ) {
				return false;
			} elseif ( $class === $this ) {
				return false;
			}
		}

		$class->set_parent( $this );
		$this->children[] = $class;

		return true;
	}

	// endregion
}
