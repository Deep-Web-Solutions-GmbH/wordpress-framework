<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Utilities\Actions\Initializable\InitializeCachingServiceTrait;
use DeepWebSolutions\Framework\Utilities\Caching\CachingServiceAwareInterface;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the splitting of permissions across multiple files.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
class AbstractPermissionsChild extends AbstractPluginFunctionality implements CachingServiceAwareInterface {
	// region TRAITS

	use InitializeCachingServiceTrait;

	// endregion

	// region METHODS

	/**
	 * Returns a list of the current instance's permissions. By default that's all the public constants of the class
	 * but inheriting classes can override this to provide a different logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	public function get_permissions(): array {
		return self::get_reflection_class()->getConstants();
	}

	/**
	 * Returns a list of the current instance's permission constants + a list of all children's recursive permission constants.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	final public function collect_permissions(): array {
		$permissions_key = "permissions_{$this->get_id()}";
		$permissions     = $this->get_cache_value( $permissions_key );

		if ( false === $permissions ) {
			$permissions = $this->get_permissions();
			foreach ( $this->get_children() as $child ) {
				if ( \is_a( $child, self::class ) ) {
					$permissions += $child->collect_permissions();
				}
			}

			$this->set_cache_value( $permissions_key, $permissions );
		}

		return $permissions;
	}

	/**
	 * Returns a definition array of how to grant this instance's permissions during the installation routine.
	 * Inheriting classes can overwrite this to change the default logic.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	public function get_granting_rules(): array {
		return array( 'administrator' => 'all' );
	}

	/**
	 * Returns a list of which roles each permission should be granted to for the current instance + all the instance's permissions children.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @return  array
	 */
	final public function collect_granting_rules(): array {
		$rules_key = "permissions_rules_{$this->get_id()}";
		$rules     = $this->get_cache_value( $rules_key );

		if ( false === $rules ) {
			$rules = \array_fill_keys( \array_values( $this->get_permissions() ), array() );

			$granting_rules = $this->get_granting_rules();
			foreach ( $granting_rules as $role => $granting_rule ) {
				if ( 'all' === $granting_rule ) {
					foreach ( $rules as &$roles ) {
						$roles[] = $role;
					}
				} elseif ( \is_array( $granting_rule ) && isset( $granting_rule['rule'], $granting_rule['permissions'] ) ) {
					if ( 'include' === $granting_rule['rule'] ) {
						foreach ( $granting_rule['permissions'] as $permission ) {
							if ( isset( $rules[ $permission ] ) ) {
								$rules[ $permission ][] = $role;
							}
						}
					} elseif ( 'exclude' === $granting_rule['rule'] ) {
						foreach ( $rules as $permission => &$roles ) {
							if ( ! \in_array( $permission, $granting_rule['permissions'], true ) ) {
								$roles[] = $role;
							}
						}
					}
				}
			}

			foreach ( $this->get_children() as $child ) {
				if ( is_a( $child, self::class ) ) {
					$rules += $child->collect_granting_rules();
				}
			}

			$this->set_cache_value( $rules_key, $rules );
		}

		return $rules;
	}

	// endregion
}
