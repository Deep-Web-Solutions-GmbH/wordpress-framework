<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UpdateFailureException;
use DeepWebSolutions\Framework\Core\Actions\InstallableInterface;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the actions of installing, updating, and removing WP capabilities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPermissionsFunctionality extends AbstractPermissionsChildFunctionality implements InstallableInterface {
	// region METHODS

	/**
	 * Returns the WordPress role objects for existing roles.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  \WP_Role[]
	 */
	public function get_existing_roles(): array {
		return \array_filter(
			\array_map(
				fn( string $role_name ) => \get_role( $role_name ),
				\array_unique( \array_merge( ...\array_values( $this->collect_granting_rules() ) ) )
			)
		);
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
		$granting_rules = $this->collect_granting_rules();
		$existing_roles = $this->get_existing_roles();

		foreach ( $existing_roles as $wp_role ) {
			foreach ( $granting_rules as $permission => $roles ) {
				if ( \in_array( $wp_role->name, $roles, true ) ) {
					$wp_role->add_cap( $permission );
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

		$permissions    = $this->collect_permissions();
		$granting_rules = $this->collect_granting_rules();
		$existing_roles = $this->get_existing_roles();

		$added_permissions   = \array_diff( $permissions, $current_version );
		$removed_permissions = \array_diff( $current_version, $permissions );

		foreach ( $existing_roles as $role ) {
			foreach ( $added_permissions as $permission ) {
				if ( \in_array( $role->name, $granting_rules[ $permission ], true ) ) {
					$role->add_cap( $permission );
				}
			}
			foreach ( $removed_permissions as $permission ) {
				$role->remove_cap( $permission );
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
		$default_caps = $this->collect_permissions();
		foreach ( \wp_roles()->role_objects as $role ) {
			foreach ( $default_caps as $capability ) {
				$role->remove_cap( $capability );
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
}
