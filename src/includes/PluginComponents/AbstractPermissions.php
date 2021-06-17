<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UpdateFailureException;
use DeepWebSolutions\Framework\Core\Actions\InstallableInterface;
use DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Helpers\DataTypes\Objects;
use DeepWebSolutions\Framework\Helpers\FileSystem\Objects\ReflectionTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the actions of installing, updating, and removing WP capabilities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPermissions extends AbstractPluginFunctionality implements InstallableInterface {
	// region METHODS

	/**
	 * Returns the WordPress role objects for existing roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  \WP_Role[]
	 */
	final protected function get_existing_roles(): array {
		return \array_filter(
			\array_map(
				fn( string $role_name ) => \get_role( $role_name ),
				\array_keys( $this->get_granting_rules() )
			)
		);
	}

	/**
	 * Inheriting classes can overwrite this to determine whether the uninstallation routine should be run or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	protected function should_remove_data_on_uninstall(): bool {
		return false;
	}

	// endregion

	// region INSTALLATION METHODS

	/**
	 * Adds the default capabilities to the default roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InstallFailureException|null
	 */
	public function install(): ?InstallFailureException {
		$granting_rules  = $this->get_granting_rules();
		$existing_roles  = $this->get_existing_roles();
		$all_permissions = $this->collect_permissions();

		foreach ( $existing_roles as $role ) {
			$rules = $granting_rules[ $role->name ];

			foreach ( $all_permissions as $capability ) {
				if ( 'all' === $rules || $this->should_grant_permission( $rules, $capability ) ) {
					$role->add_cap( $capability );
				}
			}
		}

		return null;
	}

	/**
	 * Installs newly added capabilities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string  $current_version    Currently installed version.
	 *
	 * @return  UpdateFailureException|null
	 */
	public function update( string $current_version ): ?UpdateFailureException {
		$current_version = \json_decode( $current_version, true );
		if ( \is_null( $current_version ) ) {
			return new UpdateFailureException( \__( 'Failed to update permissions', 'dws-wp-framework-core' ) );
		}

		$granting_rules  = $this->get_granting_rules();
		$existing_roles  = $this->get_existing_roles();
		$all_permissions = $this->collect_permissions();

		$extra_caps   = \array_diff( $all_permissions, $current_version );
		$removed_caps = \array_diff( $current_version, $all_permissions );

		foreach ( $existing_roles as $role ) {
			$rules = $granting_rules[ $role->name ];

			foreach ( $extra_caps as $capability ) {
				if ( 'all' === $rules || $this->should_grant_permission( $rules, $capability ) ) {
					$role->add_cap( $capability );
				}
			}
			foreach ( $removed_caps as $capability ) {
				$role->remove_cap( $capability );
			}
		}

		return null;
	}

	/**
	 * Maybe removes the installed capabilities from all roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string|null     $current_version    Currently installed version.
	 *
	 * @return  UninstallFailureException|null
	 */
	public function uninstall( ?string $current_version = null ): ?UninstallFailureException {
		if ( true === $this->should_remove_data_on_uninstall() ) {
			$default_caps = $this->collect_permissions();
			foreach ( \wp_roles()->role_objects as $role ) {
				foreach ( $default_caps as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}

		return null;
	}

	/**
	 * The permissions version is defined by the md5 hash of the constants defining said permissions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_current_version(): string {
		return \wp_json_encode( \array_values( $this->collect_permissions() ) );
	}

	// endregion

	// region HELPERS

	/**
	 * Inheriting classes can overwrite this to change the default roles to be granted permissions on installation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_granting_rules(): array {
		return array( 'administrator' => 'all' );
	}

	/**
	 * Decides whether a capability should be granted or not based on an array of rules.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $rules          The rules to base the decision on.
	 * @param   string  $capability     Capability to decide on.
	 *
	 * @return  bool
	 */
	protected function should_grant_permission( array $rules, string $capability ): bool {
		if ( ! isset( $rules['rule'], $rules['permissions'] ) ) {
			return false;
		}

		return ( 'include' === $rules['rule'] && \in_array( $capability, $rules['permissions'], true ) )
				|| ( 'exclude' === $rules['rule'] && ! \in_array( $capability, $rules['permissions'], true ) );
	}

	/**
	 * Returns a list of the current instance's constants + a list of all children's constants.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function collect_permissions(): array {
		$permissions_key = "permissions_{$this->get_id()}";
		$permissions     = \wp_cache_get( $permissions_key, $this->get_plugin()->get_plugin_slug() );

		if ( false === $permissions ) {
			$permissions = self::get_reflection_class()->getConstants();
			foreach ( $this->get_children() as $child ) {
				if ( Objects::has_trait_deep( ReflectionTrait::class, $child ) ) {
					/* @noinspection PhpUndefinedMethodInspection */
					$permissions += $child::get_reflection_class()->getConstants();
				}
			}

			\wp_cache_set( $permissions_key, $permissions, $this->get_plugin()->get_plugin_slug() );
		}

		return $permissions;
	}

	// endregion
}
